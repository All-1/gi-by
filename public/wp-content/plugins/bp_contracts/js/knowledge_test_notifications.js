"use strict";

/**
 * Knowledge test notifications (Phase 6) — popup / bottom / block (spec §3.1–§3.2, points 6–7).
 * «Закрыть» / «Понятно» hide the notice until the next calendar day or a WebSocket reconnect.
 * Block mode uses the same wait: the overlay is not shown in the visit that reached the limit.
 */

const KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH = "/tests/";
const KNOWLEDGE_TEST_NOTIFICATION_SNOOZE_KEY = "ktNotificationSnooze";
/** @deprecated removed on load */
const KNOWLEDGE_TEST_NOTIFICATION_SESSION_KEY = "ktNotificationSessionDismissed";
let knowledgeTestNotificationsHost = null;
/** True only after the socket drops while this document is still open. */
let knowledgeTestNotificationResumeAfterWsDrop = false;
let knowledgeTestNotificationPageLeaving = false;

window.addEventListener("pagehide", function () {
  knowledgeTestNotificationPageLeaving = true;
});

document.addEventListener(
  "click",
  function (event) {
    let anchor = event.target && event.target.closest ? event.target.closest("a") : null;
    if (!anchor) {
      return;
    }
    let href = anchor.getAttribute("href") || "";
    if (href.indexOf("action=logout") === -1) {
      return;
    }
    localStorage.removeItem(KNOWLEDGE_TEST_NOTIFICATION_SNOOZE_KEY);
  },
  true,
);

/** Called from communication_server.js when the socket closes. */
function noteKnowledgeTestNotificationSocketClosed() {
  if (knowledgeTestNotificationPageLeaving) {
    return;
  }
  if (sessionStorage.getItem("isPageReload") === "true") {
    return;
  }
  knowledgeTestNotificationResumeAfterWsDrop = true;
}

function knowledgeTestNotificationLocalDateString() {
  let d = new Date();
  let month = String(d.getMonth() + 1);
  let day = String(d.getDate());
  if (month.length === 1) {
    month = "0" + month;
  }
  if (day.length === 1) {
    day = "0" + day;
  }
  return d.getFullYear() + "-" + month + "-" + day;
}

function readKnowledgeTestNotificationSnoozeMap() {
  try {
    return JSON.parse(localStorage.getItem(KNOWLEDGE_TEST_NOTIFICATION_SNOOZE_KEY) || "{}");
  } catch (e) {
    return {};
  }
}

function writeKnowledgeTestNotificationSnoozeMap(map) {
  localStorage.setItem(KNOWLEDGE_TEST_NOTIFICATION_SNOOZE_KEY, JSON.stringify(map));
}

function notificationVisitKey(item) {
  return String(item.testId) + "|" + String(item.notificationType || "");
}

function snoozedLocalDateForKey(key) {
  let map = readKnowledgeTestNotificationSnoozeMap();
  let snoozedOn = map[key];
  if (snoozedOn && typeof snoozedOn === "object" && snoozedOn.date) {
    return String(snoozedOn.date);
  }
  return typeof snoozedOn === "string" ? snoozedOn : "";
}

function markKnowledgeTestNotificationSnoozed(item) {
  let key = notificationVisitKey(item);
  let map = readKnowledgeTestNotificationSnoozeMap();
  map[key] = knowledgeTestNotificationLocalDateString();
  writeKnowledgeTestNotificationSnoozeMap(map);
}

function isKnowledgeTestNotificationSnoozed(item) {
  let key = notificationVisitKey(item);
  let snoozedOn = snoozedLocalDateForKey(key);
  if (!snoozedOn) {
    return false;
  }
  return snoozedOn === knowledgeTestNotificationLocalDateString();
}

function requestKnowledgeTestNotifications() {
  if (!document.querySelector(".knowledge_tests_nav_link")) {
    return;
  }
  if (typeof socket === "undefined" || socket.readyState !== WebSocket.OPEN) {
    return;
  }
  if (knowledgeTestNotificationResumeAfterWsDrop) {
    knowledgeTestNotificationResumeAfterWsDrop = false;
    localStorage.removeItem(KNOWLEDGE_TEST_NOTIFICATION_SNOOZE_KEY);
  }
  try {
    sessionStorage.removeItem(KNOWLEDGE_TEST_NOTIFICATION_SESSION_KEY);
  } catch (e) {
    /* ignore */
  }
  socket.send("getKnowledgeTestNotifications::: " + JSON.stringify({}));
}

function handlerKnowledgeTestNotificationsOnPage(data, user) {
  if (!data || data.ok === false) {
    if (typeof requestKnowledgeTestNotifications === "function") {
      requestKnowledgeTestNotifications();
    }
    return;
  }
  renderKnowledgeTestNotifications(data);
}

function isOnKnowledgeTestsDealerPage() {
  if (typeof where !== "undefined" && where === "knowledge_tests") {
    return true;
  }
  let path = window.location.pathname.replace(/^\/|\/$/g, "");
  return path === "tests";
}

function renderKnowledgeTestNotifications(data) {
  clearKnowledgeTestNotificationUi();
  if (isOnKnowledgeTestsDealerPage()) {
    return;
  }

  let items = (data.items || []).filter(function (item) {
    return !isKnowledgeTestNotificationSnoozed(item);
  });
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

  if (blockItem !== null) {
    knowledgeTestNotificationsHost.appendChild(buildKnowledgeTestBlockOverlay(blockItem));
  }
}

function buildKnowledgeTestPopup(item) {
  let shade = createTagHtml("div", "shade-full-screen-box knowledge_test_notification_popup");
  let box = createTagHtml("div", "box-denied-notification knowledge_test_notification_dialog");
  let title = knowledgeTestNotificationTitle(item);
  let actions = createTagHtml("div", "knowledge_test_notification_actions");
  let testsLink = createKnowledgeTestNotificationLinkButton(
    "Перейти к тестам",
    KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH,
    "close-denied-notification",
    item,
  );
  let dismissBtn = createTagHtml("button", "close-denied-notification", "Закрыть");
  dismissBtn.addEventListener("click", function () {
    dismissKnowledgeTestNotification(item);
  });
  massAppendChild(
    box,
    createTagHtml("h4", "title-denied", title),
    createTagHtml("p", "text-denied-notification", knowledgeTestNotificationBody(item)),
  );
  massAppendChild(actions, testsLink, dismissBtn);
  box.appendChild(actions);
  shade.appendChild(box);
  return shade;
}

function buildKnowledgeTestBlockOverlay(item) {
  let shade = createTagHtml("div", "shade-full-screen-box knowledge_test_notification_block");
  let box = createTagHtml("div", "box-denied-notification knowledge_test_notification_dialog");
  massAppendChild(
    box,
    createTagHtml("h4", "title-denied", "Требуется пройти тест"),
    createTagHtml(
      "p",
      "text-denied-notification",
      "Пройдите тест «" + (item.testTitle || "") + "», чтобы продолжить работу в личном кабинете.",
    ),
  );
  let actions = createTagHtml("div", "knowledge_test_notification_actions");
  let testsLink = createKnowledgeTestNotificationLinkButton(
    knowledgeTestNotificationLinkLabel(item),
    KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH,
    "close-denied-notification",
    null,
  );
  let understood = null;
  if (!knowledgeTestNotificationDismissLocked(item)) {
    understood = createTagHtml("button", "close-denied-notification", "Понятно");
    understood.addEventListener("click", function () {
      dismissKnowledgeTestNotification(item);
    });
  }
  massAppendChild(actions, testsLink, understood);
  box.appendChild(actions);
  shade.appendChild(box);
  return shade;
}

function buildKnowledgeTestBottomBar(item) {
  let bar = createTagHtml("div", "enable-notification knowledge_test_notification_bottom");
  let inner = createTagHtml("div", "inner-enable-notification");
  let text = createTagHtml("div", "text-enable-notification", knowledgeTestNotificationBody(item));
  let actions = createTagHtml("div", "knowledge_test_notification_bottom_actions");
  let dismissLocked = knowledgeTestNotificationDismissLocked(item);
  let testsLink = createKnowledgeTestNotificationLinkButton(
    knowledgeTestNotificationLinkLabel(item),
    KNOWLEDGE_TEST_NOTIFICATIONS_TESTS_PATH,
    "button-accept",
    dismissLocked ? null : item,
  );
  let understood = null;
  if (!dismissLocked) {
    understood = createTagHtml("div", "button-accept", "Понятно");
    understood.addEventListener("click", function () {
      dismissKnowledgeTestNotification(item);
    });
  }
  massAppendChild(actions, testsLink, understood);
  massAppendChild(inner, text, actions);
  bar.appendChild(inner);
  return bar;
}

function createKnowledgeTestNotificationLinkButton(label, href, extraClass, item) {
  let link = document.createElement("a");
  link.className = "knowledge_test_notification_link" + (extraClass ? " " + extraClass : "");
  link.href = href;
  link.textContent = label;
  if (item) {
    link.addEventListener("click", function (event) {
      if (isOnKnowledgeTestsDealerPage()) {
        event.preventDefault();
      }
      dismissKnowledgeTestNotification(item);
    });
  }
  return link;
}

function knowledgeTestNotificationDismissLocked(item) {
  let limit = Number(item.dismissLimit);
  if (!Number.isFinite(limit)) {
    return false;
  }
  return Number(item.dismissCount) >= limit;
}

function knowledgeTestNotificationLinkLabel(item) {
  if (item.notificationType === "failed_test") {
    return "Материалы";
  }
  return "Тесты";
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
  markKnowledgeTestNotificationSnoozed(item);
  clearKnowledgeTestNotificationUi();
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
