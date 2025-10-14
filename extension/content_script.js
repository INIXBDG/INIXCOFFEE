chrome.runtime.onMessage.addListener((msg, sender, sendResponse) => {
  if (msg && msg.type === "NEW_NOTIFS") {
    console.log("Content script terima notif:", msg.payload);
    window.postMessage({ source: "EXT_NOTIF", payload: msg.payload }, "*");
  }
});
