const VERSION = "tasksk-v2";
const STATIC_CACHE = `${VERSION}-static`;
const PAGE_CACHE = `${VERSION}-pages`;
const DB_NAME = "tasksk-offline";
const STORE_NAME = "request-queue";
const STATIC_ASSETS = [
    "/offline.html",
    "/manifest.webmanifest",
    "/apple-touch-icon.png",
    "/icons/icon-192.png",
    "/icons/icon-512.png",
    "/fonts/BebasNeue-Regular.otf",
];

self.addEventListener("install", (event) => {
    event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(STATIC_ASSETS)));
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter((key) => key.startsWith("tasksk-") && ![STATIC_CACHE, PAGE_CACHE].includes(key)).map((key) => caches.delete(key)));
        await self.clients.claim();
        await replayQueue();
    })());
});

self.addEventListener("fetch", (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (url.origin !== self.location.origin) return;

    if (request.method !== "GET") {
        if (url.pathname.startsWith("/tasks")) event.respondWith(sendOrQueue(request));
        return;
    }

    if (request.mode === "navigate") {
        event.respondWith(networkFirstPage(request));
        return;
    }

    if (url.pathname.startsWith("/api/")) {
        event.respondWith(networkFirst(request, PAGE_CACHE));
        return;
    }

    event.respondWith(cacheFirst(request));
});

self.addEventListener("sync", (event) => {
    if (event.tag === "tasksk-sync") event.waitUntil(replayQueue());
});

self.addEventListener("message", (event) => {
    if (event.data?.type === "REPLAY_QUEUE") event.waitUntil(replayQueue());
    if (event.data?.type === "CACHE_CURRENT_PAGE") {
        event.waitUntil(cacheCurrentPage(event.data.url, event.data.assets || []));
    }
});

async function networkFirstPage(request) {
    try {
        const response = await fetch(request);
        if (response.ok && response.type === "basic") {
            const cache = await caches.open(PAGE_CACHE);
            await cache.put(request, response.clone());
        }
        return response;
    } catch {
        await broadcast("OFFLINE_ACTIVE");
        return (await caches.match(request)) || (await caches.match("/dashboard")) || (await caches.match("/offline.html"));
    }
}

async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) (await caches.open(cacheName)).put(request, response.clone());
        return response;
    } catch {
        const cached = await caches.match(request);
        await broadcast("OFFLINE_ACTIVE");
        return cached || new Response(JSON.stringify({ offline: true }), { status: 503, headers: { "Content-Type": "application/json" } });
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response.ok) (await caches.open(STATIC_CACHE)).put(request, response.clone());
        return response;
    } catch {
        await broadcast("OFFLINE_ACTIVE");
        return new Response("", { status: 503 });
    }
}

async function cacheCurrentPage(url, assets) {
    try {
        const pageRequest = new Request(url, { credentials: "same-origin" });
        const pageResponse = await fetch(pageRequest);
        if (pageResponse.ok) (await caches.open(PAGE_CACHE)).put(pageRequest, pageResponse.clone());

        const cache = await caches.open(STATIC_CACHE);
        await Promise.all(assets.map(async (asset) => {
            try {
                const request = new Request(asset, { credentials: "same-origin" });
                const response = await fetch(request);
                if (response.ok) await cache.put(request, response);
            } catch {
                // The core offline page is already available; optional assets can retry later.
            }
        }));
    } catch {
        // A failed warm-up is harmless and will be retried on the next online load.
    }
}

async function sendOrQueue(request) {
    try {
        return await fetch(request.clone());
    } catch {
        await queueRequest(request);
        if (self.registration.sync) await self.registration.sync.register("tasksk-sync");
        await broadcast("REQUEST_QUEUED");
        return new Response(JSON.stringify({ success: true, queued: true }), {
            status: 202,
            headers: { "Content-Type": "application/json", "X-Tasksk-Queued": "1" },
        });
    }
}

async function queueRequest(request) {
    const headers = {};
    request.headers.forEach((value, key) => { headers[key] = value; });
    const entry = {
        url: request.url,
        method: request.method,
        headers,
        body: await request.clone().text(),
        createdAt: Date.now(),
    };
    const db = await openDb();
    await transactionDone(db.transaction(STORE_NAME, "readwrite"), (store) => store.add(entry));
}

async function replayQueue() {
    const db = await openDb();
    const entries = await getAll(db);
    let synced = 0;

    for (const entry of entries) {
        try {
            const response = await fetch(entry.url, { method: entry.method, headers: entry.headers, body: entry.body, credentials: "same-origin" });
            if (!response.ok) break;
            await deleteEntry(db, entry.id);
            synced += 1;
        } catch {
            break;
        }
    }

    if (synced > 0) await broadcast("QUEUE_SYNCED");
}

function openDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = () => request.result.createObjectStore(STORE_NAME, { keyPath: "id", autoIncrement: true });
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function transactionDone(transaction, action) {
    return new Promise((resolve, reject) => {
        action(transaction.objectStore(STORE_NAME));
        transaction.oncomplete = resolve;
        transaction.onerror = () => reject(transaction.error);
    });
}

function getAll(db) {
    return new Promise((resolve, reject) => {
        const request = db.transaction(STORE_NAME).objectStore(STORE_NAME).getAll();
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function deleteEntry(db, id) {
    return transactionDone(db.transaction(STORE_NAME, "readwrite"), (store) => store.delete(id));
}

async function broadcast(type) {
    const clients = await self.clients.matchAll({ type: "window", includeUncontrolled: true });
    clients.forEach((client) => client.postMessage({ type }));
}
