import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

function ensureConnectionNotice() {
    let notice = document.getElementById("connection-notice");

    if (!notice) {
        notice = document.createElement("div");
        notice.id = "connection-notice";
        notice.setAttribute("role", "status");
        notice.style.cssText = [
            "position:fixed",
            "left:50%",
            "bottom:16px",
            "transform:translateX(-50%)",
            "z-index:1000",
            "max-width:calc(100% - 32px)",
            "padding:9px 14px",
            "border-radius:999px",
            "background:#27272f",
            "color:#fff",
            "font:500 13px/1.3 -apple-system,BlinkMacSystemFont,Segoe UI,Arial,sans-serif",
            "box-shadow:0 8px 24px rgba(0,0,0,.18)",
            "text-align:center",
            "display:none",
        ].join(";");
        document.body.appendChild(notice);
    }

    return notice;
}

let noticeTimer;
function showConnectionNotice(message, persistent = false) {
    const notice = ensureConnectionNotice();
    notice.textContent = message;
    notice.style.display = "block";
    clearTimeout(noticeTimer);

    if (!persistent) {
        noticeTimer = setTimeout(() => {
            notice.style.display = "none";
        }, 4000);
    }
}

function updateConnectionState() {
    if (navigator.onLine) {
        const notice = document.getElementById("connection-notice");
        if (notice?.dataset.offline === "true") {
            notice.dataset.offline = "false";
            showConnectionNotice("Связь восстановлена. Синхронизируем изменения…");
        }
        navigator.serviceWorker?.controller?.postMessage({ type: "REPLAY_QUEUE" });
        return;
    }

    const notice = ensureConnectionNotice();
    notice.dataset.offline = "true";
    showConnectionNotice("Офлайн-режим: изменения отправятся после подключения", true);
}

async function checkServerConnection() {
    try {
        const response = await fetch(`/offline-status-probe?time=${Date.now()}`, {
            method: "HEAD",
            cache: "no-store",
        });
        if (response.status !== 503) return;
    } catch {
        // A rejected probe also means that the application server is unreachable.
    }

    const notice = ensureConnectionNotice();
    notice.dataset.offline = "true";
    showConnectionNotice("Офлайн-режим: изменения отправятся после подключения", true);
}

window.addEventListener("online", updateConnectionState);
window.addEventListener("offline", updateConnectionState);

if ("serviceWorker" in navigator) {
    window.addEventListener("load", async () => {
        try {
            const registration = await navigator.serviceWorker.register("/service-worker.js", { scope: "/" });
            await navigator.serviceWorker.ready;
            const worker = registration.active || registration.waiting || registration.installing;
            worker?.postMessage({
                type: "CACHE_CURRENT_PAGE",
                url: window.location.href,
                assets: [...document.querySelectorAll('link[rel="stylesheet"], script[src]')]
                    .map((element) => element.href || element.src)
                    .filter(Boolean),
            });
            updateConnectionState();
            await checkServerConnection();
        } catch (error) {
            console.error("Не удалось включить офлайн-режим", error);
        }
    });

    navigator.serviceWorker.addEventListener("message", (event) => {
        if (event.data?.type === "REQUEST_QUEUED") {
            const notice = ensureConnectionNotice();
            notice.dataset.offline = "true";
            showConnectionNotice("Изменение сохранено и отправится после подключения", true);
        }
        if (event.data?.type === "OFFLINE_ACTIVE") {
            const notice = ensureConnectionNotice();
            notice.dataset.offline = "true";
            showConnectionNotice("Офлайн-режим: изменения отправятся после подключения", true);
        }
        if (event.data?.type === "QUEUE_SYNCED") {
            showConnectionNotice("Офлайн-изменения синхронизированы");
            setTimeout(() => window.location.reload(), 600);
        }
    });
}
