// obsługa zanikania messagesów
setTimeout(() => {
  var alert = document.getElementById("flash-message");
  if (alert) {
    alert.remove();
  }
}, 3000);
