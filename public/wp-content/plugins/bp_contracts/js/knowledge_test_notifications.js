"use strict";

/**
 * Knowledge test notifications (Phase 6) — popup / block / bottom bar (spec §3.1–§3.2).
 */

const KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH = "/tests/";
let knowledgeTestNotificationsHost = null;

function onKnowledgeTestsWebSocketOpen() {
  requestKnowledgeTestNotifications();
}

function requestKnowledgeTestNotifications() {
  if (!document.querySelector(".knowledge_tests_nav_link")) {
    return;
  }
  if (typeof socket === "undefined" || socket.readyState !== WebSocket.OPEN) {
    return;
  }
  socket.send("getKnowledgeTestNotifications::: " + JSON.stringify({}));
}

function handlerKnowledgeTestNotificationsOnPage(data, user) {
  if (!data || data.ok === false) {
    return;
  }
  renderKnowledgeTestNotifications(data);
}

function renderKnowledgeTestNotifications(data) {
  clearKnowledgeTestNotificationUi();
  let items = data.items || [];
  if (items.length === 0) {
    return;
  }

  knowledgeTestNotificationsHost = createTagHtml("div", "knowledge_test_notifications_host");
  document.body.appendChild(knowledgeTestNotificationsHost);

  let blockItem = null;
  let popupItem = null;
  items.forEach(function (item) {
    if (item.presentation === "block" && blockItem === null) {
      blockItem = item;
    }
    if (item.presentation === "popup" && popupItem === null) {
      popupItem = item;
    }
    if (item.presentation === "bottom") {
      knowledgeTestNotificationsHost.appendChild(buildKnowledgeTestBottomBar(item));
    }
  });

  if (popupItem !== null) {
    knowledgeTestNotificationsHost.appendChild(buildKnowledgeTestPopup(popupItem));
  }

  if (blockItem !== null && shouldApplyKnowledgeTestAccountBlock()) {
    knowledgeTestNotificationsHost.appendChild(buildKnowledgeTestBlockOverlay(blockItem));
  }
}

function shouldApplyKnowledgeTestAccountBlock() {
  return typeof where === "undefined" || where !== "knowledge_tests";
}

function buildKnowledgeTestPopup(item) {
  let shade = createTagHtml("div", "shade-full-screen-box knowledge_test_notification_popup");
  let box = createTagHtml("div", "box-denied-notification knowledge_test_notification_dialog");
  let title = knowledgeTestNotificationTitle(item);
  box.appendChild(createTagHtml("h4", "title-denied", title));
  box.appendChild(
    createTagHtml("p", "text-denied-notification", knowledgeTestNotificationBody(item)),
  );
  let actions = createTagHtml("div", "knowledge_test_notification_actions");
  actions.appendChild(
    createKnowledgeTestNotificationLinkButton("Перейти к тестам", KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH),
  );
  if (item.materialsAvailable) {
    actions.appendChild(
      createKnowledgeTestNotificationLinkButton("Материалы", KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH),
    );
  }
  let dismissBtn = createTagHtml("button", "close-denied-notification", "Закрыть");
  dismissBtn.addEventListener("click", function () {
    dismissKnowledgeTestNotification(item);
    shade.remove();
  });
  actions.appendChild(dismissBtn);
  box.appendChild(actions);
  shade.appendChild(box);
  return shade;
}

function buildKnowledgeTestBlockOverlay(item) {
  let shade = createTagHtml("div", "shade-full-screen-box knowledge_test_notification_block");
  let box = createTagHtml("div", "box-denied-notification knowledge_test_notification_dialog");
  box.appendChild(createTagHtml("h4", "title-denied", "Требуется пройти тест"));
  box.appendChild(
    createTagHtml(
      "p",
      "text-denied-notification",
      "Пройдите тест «" + (item.testTitle || "") + "», чтобы продолжить работу в личном кабинете.",
    ),
  );
  box.appendChild(
    createKnowledgeTestNotificationLinkButton("Перейти к тестам", KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH),
  );
  shade.appendChild(box);
  return shade;
}

function buildKnowledgeTestBottomBar(item) {
  let bar = createTagHtml("div", "knowledge_test_notification_bottom");
  let text = createTagHtml("div", "knowledge_test_notification_bottom_text", knowledgeTestNotificationBody(item));
  let actions = createTagHtml("div", "knowledge_test_notification_bottom_actions");
  actions.appendChild(
    createKnowledgeTestNotificationLinkButton("Тесты", KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH, "knowledge_test_notification_bottom_link"),
  );
  if (item.materialsAvailable) {
    actions.appendChild(
      createKnowledgeTestNotificationLinkButton("Материалы", KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH, "knowledge_test_notification_bottom_link"),
    );
  }
  let understood = createTagHtml("button", "knowledge_test_notification_understood", "Понятно", "", "button");
  understood.addEventListener("click", function () {
    dismissKnowledgeTestNotification(item);
  });
  actions.appendChild(understood);
  bar.appendChild(text);
  bar.appendChild(actions);
  return bar;
}

function createKnowledgeTestNotificationLinkButton(label, href, extraClass) {
  let link = document.createElement("a");
  link.className = "knowledge_test_notification_link" + (extraClass ? " " + extraClass : "");
  link.href = href;
  link.textContent = label;
  return link;
}

function knowledgeTestNotificationTitle(item) {
  if (item.notificationType === "failed_test") {
    return "Не сдан тест";
  }
  return "Доступен новый тест";
}

function knowledgeTestNotificationBody(item) {
  let title = item.testTitle || "тест";
  if (item.notificationType === "failed_test") {
    return "Изучите материалы по тесту «" + title + "» перед следующей попыткой.";
  }
  return "Доступен тест «" + title + "». Перейдите в раздел «Тесты».";
}

function dismissKnowledgeTestNotification(item) {
  if (typeof socket === "undefined" || socket.readyState !== WebSocket.OPEN) {
    return;
  }
  socket.send(
    "dismissKnowledgeTestNotification::: " +
      JSON.stringify({
        testId: item.testId,
        notificationType: item.notificationType,
      }),
  );
}

function clearKnowledgeTestNotificationUi() {
  if (knowledgeTestNotificationsHost) {
    knowledgeTestNotificationsHost.remove();
    knowledgeTestNotificationsHost = null;
  }
}
