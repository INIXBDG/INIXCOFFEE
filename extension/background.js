let intervalMs = 2000;
let lastNotifId = 0;

async function pollServer() {
  try {
    const res = await fetch(`http://127.0.0.1:8000/notifications?after_id=${lastNotifId}`);
    if (!res.ok) return;
    const data = await res.json();

    if (data.length > 0) {
      lastNotifId = data[data.length - 1].id || lastNotifId;

      const tabs = await chrome.tabs.query({ url: "http://127.0.0.1:8000/*" });
      for (const t of tabs) {
        chrome.tabs.sendMessage(t.id, {
          type: "NEW_NOTIFS",
          payload: data.slice(-2)
        });
      }
    }
  } catch (e) {
    console.error("background poll error", e);
  } finally {
    setTimeout(pollServer, intervalMs);
  }
}

pollServer();
