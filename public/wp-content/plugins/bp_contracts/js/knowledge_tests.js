"use strict";

/**
 * Knowledge tests — personal account page (same pattern as orders.js / analytics.js).
 * WS commands: showTests, startTest, … — responses: handlerTestsOnPage, …
 */

const where = "knowledge_tests";

let knowledgeTestsPage = 1;
let knowledgeTestsPerPage = 10;
let knowledgeTestsOverlayTestId = null;
let knowledgeTestsOverlayTitle = "";
let knowledgeTestsFinishPending = false;

const KNOWLEDGE_TESTS_STATUS_LABELS = {
  not_started: "Не начат",
  in_progress: "В процессе",
  failed: "Не сдан",
  passed: "Сдан",
  outdated: "Устарел",
};

function setDefaultState(where) {
  if (typeof socket === "undefined" || socket.readyState !== WebSocket.OPEN) {
    return;
  }
  sendShowTests(knowledgeTestsPage, knowledgeTestsPerPage);
}

function sendShowTests(page, perPage) {
  knowledgeTestsPage = page;
  knowledgeTestsPerPage = perPage;
  socket.send(
    "showTests::: " + JSON.stringify({ page: knowledgeTestsPage, perPage: knowledgeTestsPerPage }),
  );
}

function sendKnowledgeTestsCommand(command, body) {
  socket.send(command + "::: " + JSON.stringify(body));
}

function initKnowledgeTestsPage() {
  bindKnowledgeTestsPerPage();
  let closeBtn = document.getElementById("close_knowledge_tests_overlay");
  if (closeBtn) {
    closeBtn.addEventListener("click", closeKnowledgeTestsOverlay);
  }
  document.addEventListener("keydown", function (event) {
    if (event.key !== "Escape") {
      return;
    }
    let overlay = document.getElementById("knowledge_tests_overlay");
    if (overlay && overlay.style.display !== "none") {
      closeKnowledgeTestsOverlay();
    }
  });
}

initKnowledgeTestsPage();

/** WS reconnect on /tests/ — pathname maps to handlerTests, not handlerTestsOnPage */
function handlerTests(user) {
  setDefaultState(where);
}

function handlerTestsOnPage(data, user) {
  updateKnowledgeTestsNavFromList(data);
  if (!data || data.ok === false) {
    renderKnowledgeTestsListError(data);
    return;
  }
  renderKnowledgeTestsList(data);
}

function handlerTestQuestionOnPage(data, user) {
  if (!data || data.ok === false) {
    showKnowledgeTestsOverlayError(data);
    return;
  }
  renderKnowledgeTestsQuestion(data);
}

function handlerTestFinishedOnPage(data, user) {
  knowledgeTestsFinishPending = false;
  clearKnowledgeTestsDisconnectBanner();
  if (!data || data.ok === false) {
    showKnowledgeTestsOverlayError(data);
    return;
  }
  renderKnowledgeTestsFinished(data);
  if (typeof requestKnowledgeTestNotifications === "function") {
    requestKnowledgeTestNotifications();
  }
}

function handlerTestMaterialsOnPage(data, user) {
  if (!data || data.ok === false) {
    showKnowledgeTestsOverlayError(data);
    return;
  }
  if (!data.items || data.items.length === 0) {
    closeKnowledgeTestsOverlay();
    return;
  }
  renderKnowledgeTestsMaterials(data);
}

function renderKnowledgeTestsList(data) {
  let container = document.getElementById("main_container_knowledge_tests");
  if (!container) {
    return;
  }
  container.innerHTML = "";
  let tests = data.tests || [];
  if (tests.length === 0) {
    container.appendChild(
      createTagHtml("div", "knowledge_tests_empty", "Нет доступных тестов."),
    );
    renderKnowledgeTestsPagination(data.pagination || {});
    return;
  }
  tests.forEach(function (row) {
    container.appendChild(buildKnowledgeTestsListRow(row));
  });
  renderKnowledgeTestsPagination(data.pagination || {});
}

function buildKnowledgeTestsListRow(row) {
  let line = createTagHtml("div", "tr_div_knowledge_tests");
  let titleCell = createTagHtml("div", "td_div_knowledge_tests kt-col-title");
  titleCell.appendChild(createTagHtml("div", "knowledge_tests_row_title", row.title || ""));
  if (row.achievementArea) {
    titleCell.appendChild(
      createTagHtml("div", "knowledge_tests_row_area", row.achievementArea),
    );
  }
  let statusCode = row.displayStatus || "";
  let status = createTagHtml(
    "div",
    "td_div_knowledge_tests kt-col-status knowledge_tests_status--" + statusCode,
    formatKnowledgeTestsStatus(statusCode),
  );
  let scoreText = formatKnowledgeTestsScore(row.score);
  let score = createTagHtml("div", "td_div_knowledge_tests kt-col-score", scoreText);
  let medal = createTagHtml("div", "td_div_knowledge_tests kt-col-medal");
  fillKnowledgeTestsMedalCell(medal, row);
  let actions = createTagHtml("div", "td_div_knowledge_tests kt-col-actions");
  if (statusCode === "in_progress") {
    actions.appendChild(
      createKnowledgeTestsActionButton("Продолжить", function () {
        openKnowledgeTestsOverlay(row.testId, row.title, "startTest");
      }),
    );
  } else if (row.canStart) {
    actions.appendChild(
      createKnowledgeTestsActionButton("Начать", function () {
        openKnowledgeTestsOverlay(row.testId, row.title, "startTest");
      }),
    );
  } else if (knowledgeTestsShowDisabledStart(row)) {
    actions.appendChild(createKnowledgeTestsActionButton("Начать", null, true));
  }
  if (row.canOpenMaterials) {
    actions.appendChild(
      createKnowledgeTestsActionButton("Материалы", function () {
        openKnowledgeTestsOverlay(row.testId, row.title, "getTestMaterials");
      }),
    );
  } else if (statusCode === "failed") {
    actions.appendChild(createKnowledgeTestsActionButton("Материалы", null, true));
  }
  massAppendChild(line, titleCell, status, score, medal, actions);
  return line;
}

function knowledgeTestsShowDisabledStart(row) {
  let reasons = row.retakeReasons || [];
  if (reasons.indexOf("test_locked") !== -1) {
    return false;
  }
  let statusCode = row.displayStatus || "";
  return statusCode === "not_started"
    || statusCode === "failed"
    || statusCode === "passed"
    || statusCode === "outdated";
}

function createKnowledgeTestsActionButton(label, onClick, disabled) {
  let btn = createTagHtml("button", "knowledge_tests_action_btn", label, "", "button");
  if (disabled) {
    btn.disabled = true;
    btn.setAttribute("aria-disabled", "true");
  } else if (onClick) {
    btn.addEventListener("click", onClick);
  }
  return btn;
}

function formatKnowledgeTestsStatus(code) {
  return KNOWLEDGE_TESTS_STATUS_LABELS[code] || code || "—";
}

function formatKnowledgeTestsScore(score) {
  if (score == null || score === "") {
    return "—";
  }
  return String(score) + "%";
}

function bindKnowledgeTestsPerPage() {
  let selected = document.querySelector(".knowledge_tests_page .perpage-number-selected");
  let list = document.querySelector(".knowledge_tests_page .perpage-number-list");
  if (!selected || !list) {
    return;
  }
  let initial = parseInt(selected.textContent, 10);
  if (initial > 0) {
    knowledgeTestsPerPage = initial;
  }
  list.addEventListener("click", function (event) {
    let item = event.target.closest("div");
    if (!item || item.parentElement !== list) {
      return;
    }
    let value = parseInt(item.textContent, 10);
    if (!value) {
      return;
    }
    list.querySelectorAll("div").forEach(function (node) {
      node.classList.remove("perpage-number-enable");
      node.classList.add("perpage-number-disable");
    });
    item.classList.remove("perpage-number-disable");
    item.classList.add("perpage-number-enable");
    selected.textContent = String(value);
    knowledgeTestsPerPage = value;
    if (typeof socket !== "undefined" && socket.readyState === WebSocket.OPEN) {
      sendShowTests(1, value);
    }
  });
}

function renderKnowledgeTestsPagination(pagination) {
  let box = document.getElementById("pagination");
  if (!box || typeof packegePagination !== "function") {
    return;
  }
  let page = pagination.page || 1;
  let perPage = pagination.perPage || knowledgeTestsPerPage;
  let total = pagination.total || 0;
  let pages = total > 0 ? Math.ceil(total / perPage) : 0;
  box.innerHTML = "";
  if (pages <= 1) {
    return;
  }
  packegePagination(page, pages, box);
  box.querySelectorAll(".pagination-page").forEach(function (span) {
    if (span.id === "current-page") {
      return;
    }
    span.addEventListener("click", function () {
      let next = parseInt(span.getAttribute("data-page-admin"), 10);
      if (next > 0) {
        sendShowTests(next, perPage);
      }
    });
  });
}

function knowledgeTestsReferenceHref(raw) {
  let value = String(raw || "").trim();
  if (/^https?:\/\//i.test(value) || value.charAt(0) === "/") {
    return value;
  }
  if (value.indexOf("www.") === 0) {
    return "https://" + value;
  }
  return "";
}

function appendKnowledgeTestsReference(parent, reference) {
  let value = String(reference || "").trim();
  if (!value) {
    return;
  }
  let href = knowledgeTestsReferenceHref(value);
  let paragraph = createTagHtml("p", "knowledge_tests_reference");
  if (!href) {
    paragraph.textContent = value;
    parent.appendChild(paragraph);
    return;
  }
  let link = document.createElement("a");
  link.className = "knowledge_tests_reference_link";
  link.href = href;
  link.target = "_blank";
  link.rel = "noopener noreferrer";
  link.textContent = value;
  paragraph.appendChild(link);
  parent.appendChild(paragraph);
}

function renderKnowledgeTestsListError(data) {
  let container = document.getElementById("main_container_knowledge_tests");
  if (!container) {
    return;
  }
  container.innerHTML = "";
  let msg = knowledgeTestsErrorMessage(data);
  container.appendChild(createTagHtml("div", "knowledge_tests_error", msg));
}

function openKnowledgeTestsOverlay(testId, title, command) {
  knowledgeTestsOverlayTestId = testId;
  knowledgeTestsOverlayTitle = title || "";
  let overlay = document.getElementById("knowledge_tests_overlay");
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!overlay || !content) {
    return;
  }
  overlay.style.display = "block";
  overlay.setAttribute("aria-hidden", "false");
  let closeBtn = document.getElementById("close_knowledge_tests_overlay");
  if (closeBtn) {
    closeBtn.focus();
  }
  content.innerHTML = "";
  massAppendChild(
    content,
    createTagHtml("h2", "knowledge_tests_overlay_title", title || ""),
    createTagHtml("p", "knowledge_tests_loading", "Загрузка…"),
  );
  let body = { testId: testId };
  if (command === "startTest") {
    sendKnowledgeTestsCommand("startTest", body);
  } else if (command === "getTestMaterials") {
    sendKnowledgeTestsCommand("getTestMaterials", body);
  }
}

function closeKnowledgeTestsOverlay() {
  let overlay = document.getElementById("knowledge_tests_overlay");
  if (overlay) {
    overlay.style.display = "none";
    overlay.setAttribute("aria-hidden", "true");
  }
  knowledgeTestsFinishPending = false;
  knowledgeTestsOverlayTestId = null;
  knowledgeTestsOverlayTitle = "";
  clearKnowledgeTestsDisconnectBanner();
  sendShowTests(knowledgeTestsPage, knowledgeTestsPerPage);
}

function sendFinishKnowledgeTest(testId) {
  knowledgeTestsFinishPending = true;
  clearKnowledgeTestsDisconnectBanner();
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (content) {
    let existing = content.querySelector(".knowledge_tests_finish_saving");
    if (!existing) {
      content.appendChild(
        createTagHtml("p", "knowledge_tests_finish_saving", "Сохранение результата…"),
      );
    }
  }
  sendKnowledgeTestsCommand("finishTest", { testId: testId });
}

function onKnowledgeTestsWebSocketClose() {
  if (!knowledgeTestsOverlayTestId) {
    return;
  }
  let overlay = document.getElementById("knowledge_tests_overlay");
  if (!overlay || overlay.style.display === "none") {
    return;
  }
  if (knowledgeTestsFinishPending) {
    showKnowledgeTestsFinishWaitingBanner();
    return;
  }
  showKnowledgeTestsDisconnectBanner();
}

function onKnowledgeTestsWebSocketOpen() {
  if (!knowledgeTestsOverlayTestId) {
    return;
  }
  let overlay = document.getElementById("knowledge_tests_overlay");
  if (!overlay || overlay.style.display === "none") {
    return;
  }
  clearKnowledgeTestsDisconnectBanner();
  if (knowledgeTestsFinishPending) {
    sendKnowledgeTestsCommand("finishTest", { testId: knowledgeTestsOverlayTestId });
    return;
  }
  sendKnowledgeTestsCommand("startTest", { testId: knowledgeTestsOverlayTestId });
}

function showKnowledgeTestsDisconnectBanner() {
  showKnowledgeTestsOverlayBanner(
    "knowledge_tests_disconnect_banner",
    "Соединение прервано. Не закрывайте окно — после восстановления связи тест продолжится с того же места (до перезапуска сервера в 03:00).",
  );
}

function showKnowledgeTestsFinishWaitingBanner() {
  showKnowledgeTestsOverlayBanner(
    "knowledge_tests_disconnect_banner",
    "Сохранение результата… Связь прервалась; после переподключения попытка завершится автоматически. Не закрывайте окно.",
  );
}

function showKnowledgeTestsOverlayBanner(elementId, message) {
  let overlay = document.getElementById("knowledge_tests_overlay");
  if (!overlay) {
    return;
  }
  let banner = document.getElementById(elementId);
  if (banner) {
    banner.textContent = message;
    return;
  }
  banner = createTagHtml("div", elementId, message);
  overlay.insertBefore(banner, overlay.firstChild);
}

function clearKnowledgeTestsDisconnectBanner() {
  let banner = document.getElementById("knowledge_tests_disconnect_banner");
  if (banner) {
    banner.remove();
  }
}

function showKnowledgeTestsOverlayError(data) {
  knowledgeTestsFinishPending = false;
  clearKnowledgeTestsDisconnectBanner();
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  let loading = content.querySelector(".knowledge_tests_loading");
  if (loading) {
    loading.remove();
  }
  let msg = knowledgeTestsErrorMessage(data);
  if (data && data.error && data.error.code === "test_in_progress") {
    msg += " Завершите или продолжите уже открытый тест в списке.";
  }
  content.appendChild(createTagHtml("div", "knowledge_tests_error", msg));
}

function knowledgeTestsErrorMessage(data) {
  if (!data || !data.error) {
    return "Не удалось выполнить запрос.";
  }
  let code = data.error.code || "";
  let reasons = data.error.reasons || [];
  let codeLabels = {
    retake_delay: "Повторная попытка пока недоступна",
    pending_materials: "Сначала изучите материалы",
    test_locked: "Тест заблокирован",
    notification_blocked: "Тест недоступен (уведомление)",
    test_in_progress: "Уже открыт другой тест",
    no_session: "Сессия теста не найдена",
    invalid_test: "Тест не найден",
    validation: "Некорректные данные",
  };
  if (code === "invalid_test" && reasons.indexOf("test_not_scorable") !== -1) {
    return "Тест не настроен для оценки (отметьте правильные ответы в админке)";
  }
  if (code === "validation" && reasons.indexOf("incomplete_session") !== -1) {
    return "Ответьте на все вопросы перед завершением";
  }
  if (code === "server_error") {
    if (reasons.indexOf("attempt_insert_failed") !== -1) {
      return "Не удалось записать результат в базу. Проверьте, что таблицы gi_new_test_* созданы (активация плагина knowledge tests).";
    }
    return "Не удалось сохранить результат. Попробуйте «Завершить тест» ещё раз или обновите страницу.";
  }
  let label = codeLabels[code] || code;
  return label + (reasons.length ? " (" + reasons.join(", ") + ")" : "");
}

function renderKnowledgeTestsQuestion(data) {
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  content.innerHTML = "";
  clearKnowledgeTestsDisconnectBanner();

  if (data.sessionComplete && !data.question) {
    renderKnowledgeTestsReadyToFinish(content, data);
    return;
  }

  let progress = data.progress || {};
  let q = data.question || {};
  let inFeedback = Boolean(data.feedback);
  let questionNumber = formatKnowledgeTestsQuestionNumber(progress, inFeedback);

  massAppendChild(
    content,
    createTagHtml("p", "knowledge_tests_progress", questionNumber),
    createTagHtml("p", "knowledge_tests_question_text", q.text || ""),
  );

  let form = createTagHtml("div", "knowledge_tests_answers");
  let rightSet = knowledgeTestsIdSet(data.feedback ? data.feedback.rightAnswerIds : []);
  let wrongSet = knowledgeTestsIdSet(data.feedback ? data.feedback.wrongAnswerIds : []);

  (q.answerOptions || []).forEach(function (opt) {
    let rowClass = "knowledge_tests_answer_row";
    if (inFeedback) {
      if (wrongSet[opt.answerId]) {
        rowClass += " knowledge_tests_answer_row--wrong";
      } else if (rightSet[opt.answerId]) {
        rowClass += " knowledge_tests_answer_row--right";
      }
    }
    let row = createTagHtml("label", rowClass);
    let input = createTagHtml("input", "", "", "kt_a_" + opt.answerId, "checkbox");
    input.value = opt.answerId;
    input.name = "kt_answer";
    if (inFeedback) {
      input.disabled = true;
      if (wrongSet[opt.answerId] || rightSet[opt.answerId]) {
        input.checked = true;
      }
    }
    massAppendChild(row, input, document.createTextNode(" " + (opt.text || "")));
    form.appendChild(row);
  });
  content.appendChild(form);

  if (inFeedback) {
    renderKnowledgeTestsFeedback(content, data.feedback);
  }

  let actions = createTagHtml("div", "knowledge_tests_overlay_actions");
  if (inFeedback) {
    if (data.sessionComplete) {
      actions.appendChild(
        createKnowledgeTestsActionButton("Завершить тест", function () {
          sendFinishKnowledgeTest(data.testId);
        }),
      );
    } else {
      actions.appendChild(
        createKnowledgeTestsActionButton("Следующий вопрос", function () {
          sendKnowledgeTestsCommand("startTest", { testId: data.testId });
        }),
      );
    }
  } else {
    actions.appendChild(
      createKnowledgeTestsActionButton("Далее", function () {
        submitKnowledgeTestsQuestion(data.testId, form);
      }),
    );
  }
  content.appendChild(actions);
}

function formatKnowledgeTestsQuestionNumber(progress, inFeedback) {
  let total = progress.total || "—";
  let index = progress.index || 0;
  if (inFeedback && index > 0) {
    index -= 1;
  }
  if (index < 1 && progress.total) {
    index = 1;
  }
  return "Вопрос " + index + " из " + total;
}

function knowledgeTestsIdSet(ids) {
  let set = {};
  (ids || []).forEach(function (id) {
    set[id] = true;
  });
  return set;
}

function renderKnowledgeTestsReadyToFinish(content, data) {
  let progress = data.progress || {};
  massAppendChild(
    content,
    createTagHtml(
      "p",
      "knowledge_tests_progress",
      "Отвечено " + (progress.total || "—") + " из " + (progress.total || "—"),
    ),
    createTagHtml("p", "", "Все вопросы пройдены. Нажмите «Завершить тест», чтобы сохранить результат."),
  );
  let actions = createTagHtml("div", "knowledge_tests_overlay_actions");
  actions.appendChild(
    createKnowledgeTestsActionButton("Завершить тест", function () {
      sendFinishKnowledgeTest(data.testId);
    }),
  );
  content.appendChild(actions);
}

function submitKnowledgeTestsQuestion(testId, form) {
  let selected = [];
  form.querySelectorAll('input[name="kt_answer"]:checked').forEach(function (input) {
    selected.push(parseInt(input.value, 10));
  });
  if (selected.length === 0) {
    let err = document.querySelector(".knowledge_tests_answer_required");
    if (!err) {
      form.parentNode.appendChild(
        createTagHtml("p", "knowledge_tests_answer_required knowledge_tests_error", "Выберите хотя бы один ответ."),
      );
    }
    return;
  }
  let err = document.querySelector(".knowledge_tests_answer_required");
  if (err) {
    err.remove();
  }
  sendKnowledgeTestsCommand("submitTestQuestion", {
    testId: testId,
    selectedAnswerIds: selected,
  });
}

function renderKnowledgeTestsFeedback(content, feedback) {
  let block = createTagHtml("div", "knowledge_tests_feedback");
  if (feedback.explanation) {
    block.appendChild(createTagHtml("p", "", feedback.explanation));
  } else {
    block.appendChild(createTagHtml("p", "knowledge_tests_feedback_ok", "Ответ принят."));
  }
  appendKnowledgeTestsReference(block, feedback.reference);
  content.appendChild(block);
}

function renderKnowledgeTestsFinished(data) {
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  content.innerHTML = "";
  clearKnowledgeTestsDisconnectBanner();
  updateKnowledgeTestsNavFromList({ ok: true, navFailedHighlight: attemptStatusFailed(data) });
  let attempt = data.attempt || {};
  content.appendChild(
    createTagHtml(
      "p",
      "",
      "Результат: " + formatKnowledgeTestsStatus(attempt.status || "") + ", оценка: " + formatKnowledgeTestsScore(attempt.score),
    ),
  );
  if (attempt.validAnswers != null && attempt.invalidAnswers != null) {
    content.appendChild(
      createTagHtml(
        "p",
        "knowledge_tests_score_breakdown",
        "Подсчёт: верных выборов " +
          attempt.validAnswers +
          ", ошибочных " +
          attempt.invalidAnswers +
          ". Оценка = (все правильные − ошибочные) / все правильные; для сдачи обычно нужно не менее 70%.",
      ),
    );
  }
  if (data.medalId != null || data.medalTier) {
    let medalRow = createTagHtml("p", "knowledge_tests_finish_medal");
    medalRow.appendChild(document.createTextNode("Медаль: "));
    let badge =
      typeof createKnowledgeTestsMedalBadge === "function"
        ? createKnowledgeTestsMedalBadge({
            tierValue: data.medalTier,
            rankName: data.medalRankName || formatKnowledgeTestsMedalLabel(data.medalTier, data.medalId),
            achievementArea: data.achievementArea || knowledgeTestsOverlayTitle || "",
            size: "md",
          })
        : null;
    if (badge) {
      medalRow.appendChild(badge);
    } else {
      medalRow.appendChild(
        document.createTextNode(formatKnowledgeTestsMedalLabel(data.medalTier, data.medalId)),
      );
    }
    content.appendChild(medalRow);
  }
  let actions = createTagHtml("div", "knowledge_tests_overlay_actions");
  let materialsBtn = null;
  if (data.materialsRequired) {
    content.appendChild(
      createTagHtml("p", "", "Изучите материалы перед повторной попыткой."),
    );
    let materialsTestId = (attempt.testId || knowledgeTestsOverlayTestId);
    materialsBtn = createKnowledgeTestsActionButton("Открыть материалы", function () {
      sendKnowledgeTestsCommand("getTestMaterials", { testId: materialsTestId });
    });
  }
  massAppendChild(
    actions,
    materialsBtn,
    createKnowledgeTestsActionButton("Закрыть", function () {
      closeKnowledgeTestsOverlay();
    }),
  );
  content.appendChild(actions);
}

function attemptStatusFailed(data) {
  let attempt = data && data.attempt ? data.attempt : {};
  return (attempt.status || "") === "failed";
}

function renderKnowledgeTestsMaterials(data) {
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  content.innerHTML = "";
  content.appendChild(
    createTagHtml("h2", "knowledge_tests_overlay_title", knowledgeTestsOverlayTitle || "Материалы"),
  );
  let reviewedCount = 0;
  let totalItems = (data.items || []).length;
  let examinedBtn = createKnowledgeTestsActionButton("Изучено", function () {
    sendKnowledgeTestsCommand("confirmTestMaterialsExamined", {
      testId: data.testId,
      attemptId: data.attemptId,
    });
  });
  examinedBtn.disabled = true;

  function syncExaminedButton() {
    examinedBtn.disabled = reviewedCount < totalItems;
  }

  (data.items || []).forEach(function (item, index) {
    let block = createTagHtml("div", "knowledge_tests_material_block");
    massAppendChild(
      block,
      createTagHtml("h3", "", item.questionText || ""),
      createTagHtml("p", "", item.explanation || ""),
    );
    appendKnowledgeTestsReference(block, item.reference);
    let label = createTagHtml("label", "knowledge_tests_material_reviewed");
    let checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "knowledge_tests_material_reviewed_cb";
    checkbox.addEventListener("change", function () {
      if (checkbox.checked) {
        reviewedCount += 1;
      } else {
        reviewedCount -= 1;
      }
      syncExaminedButton();
    });
    massAppendChild(label, checkbox, document.createTextNode(" Просмотрено"));
    block.appendChild(label);
    content.appendChild(block);
  });
  let actions = createTagHtml("div", "knowledge_tests_overlay_actions");
  massAppendChild(
    actions,
    examinedBtn,
    createKnowledgeTestsActionButton("Закрыть", function () {
      closeKnowledgeTestsOverlay();
    }),
  );
  content.appendChild(actions);
}
