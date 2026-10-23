"use strict";

/**
 * Knowledge tests — personal account page (same pattern as orders.js / analytics.js).
 * WS commands: showTests, startTest, … — responses: handlerTestsOnPage, …
 */

const where = "knowledge_tests";

let knowledgeTestsPage = 1;
let knowledgeTestsPerPage = 20;
let knowledgeTestsOverlayTestId = null;

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
  let closeBtn = document.getElementById("close_knowledge_tests_overlay");
  if (closeBtn) {
    closeBtn.addEventListener("click", closeKnowledgeTestsOverlay);
  }
}

initKnowledgeTestsPage();

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
  if (!data || data.ok === false) {
    showKnowledgeTestsOverlayError(data);
    return;
  }
  renderKnowledgeTestsFinished(data);
}

function handlerTestMaterialsOnPage(data, user) {
  if (!data || data.ok === false) {
    showKnowledgeTestsOverlayError(data);
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
  let medal = createTagHtml(
    "div",
    "td_div_knowledge_tests kt-col-medal",
    formatKnowledgeTestsMedal(row.medalId),
  );
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
  } else if (statusCode === "not_started" || statusCode === "failed" || statusCode === "outdated") {
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

function formatKnowledgeTestsMedal(medalId) {
  if (medalId == null || medalId === "") {
    return "—";
  }
  return "★ " + medalId;
}

function renderKnowledgeTestsPagination(pagination) {
  let box = document.getElementById("pagination_knowledge_tests");
  if (!box) {
    return;
  }
  let page = pagination.page || 1;
  let perPage = pagination.perPage || knowledgeTestsPerPage;
  let total = pagination.total || 0;
  let pages = Math.max(1, Math.ceil(total / perPage));
  box.innerHTML = "";
  if (total === 0) {
    return;
  }
  let info = createTagHtml(
    "span",
    "knowledge_tests_page_info",
    "Страница " + page + " из " + pages + " (" + total + ")",
  );
  box.appendChild(info);
  if (page > 1) {
    box.appendChild(
      createKnowledgeTestsActionButton("←", function () {
        sendShowTests(page - 1, perPage);
      }),
    );
  }
  if (page < pages) {
    box.appendChild(
      createKnowledgeTestsActionButton("→", function () {
        sendShowTests(page + 1, perPage);
      }),
    );
  }
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
  let overlay = document.getElementById("knowledge_tests_overlay");
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!overlay || !content) {
    return;
  }
  overlay.style.display = "block";
  overlay.setAttribute("aria-hidden", "false");
  content.innerHTML = "";
  content.appendChild(createTagHtml("h2", "knowledge_tests_overlay_title", title || ""));
  content.appendChild(createTagHtml("p", "knowledge_tests_loading", "Загрузка…"));
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
  knowledgeTestsOverlayTestId = null;
  sendShowTests(knowledgeTestsPage, knowledgeTestsPerPage);
}

function showKnowledgeTestsOverlayError(data) {
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  let loading = content.querySelector(".knowledge_tests_loading");
  if (loading) {
    loading.remove();
  }
  content.appendChild(
    createTagHtml("div", "knowledge_tests_error", knowledgeTestsErrorMessage(data)),
  );
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
  let label = codeLabels[code] || code;
  return label + (reasons.length ? " (" + reasons.join(", ") + ")" : "");
}

function renderKnowledgeTestsQuestion(data) {
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  content.innerHTML = "";
  let progress = data.progress || {};
  let q = data.question || {};
  content.appendChild(
    createTagHtml(
      "p",
      "knowledge_tests_progress",
      "Вопрос " + ((progress.index || 0) + 1) + " из " + (progress.total || "—"),
    ),
  );
  content.appendChild(createTagHtml("p", "knowledge_tests_question_text", q.text || ""));
  let form = createTagHtml("div", "knowledge_tests_answers");
  (q.answerOptions || []).forEach(function (opt) {
    let row = createTagHtml("label", "knowledge_tests_answer_row");
    let input = createTagHtml("input", "", "", "kt_a_" + opt.answerId, "checkbox");
    input.value = opt.answerId;
    input.name = "kt_answer";
    row.appendChild(input);
    row.appendChild(document.createTextNode(" " + (opt.text || "")));
    form.appendChild(row);
  });
  content.appendChild(form);
  let continueBtn = createKnowledgeTestsActionButton("Далее", function () {
    submitKnowledgeTestsQuestion(data.testId, form);
  });
  content.appendChild(continueBtn);
  if (data.feedback && data.feedback.show) {
    renderKnowledgeTestsFeedback(content, data.feedback);
  }
  if (data.sessionComplete) {
    content.appendChild(
      createKnowledgeTestsActionButton("Завершить тест", function () {
        sendKnowledgeTestsCommand("finishTest", { testId: data.testId });
      }),
    );
  }
}

function submitKnowledgeTestsQuestion(testId, form) {
  let selected = [];
  form.querySelectorAll('input[name="kt_answer"]:checked').forEach(function (input) {
    selected.push(parseInt(input.value, 10));
  });
  sendKnowledgeTestsCommand("submitTestQuestion", {
    testId: testId,
    selectedAnswerIds: selected,
  });
}

function renderKnowledgeTestsFeedback(content, feedback) {
  let block = createTagHtml("div", "knowledge_tests_feedback");
  if (feedback.explanation) {
    block.appendChild(createTagHtml("p", "", feedback.explanation));
  }
  if (feedback.reference) {
    block.appendChild(createTagHtml("p", "knowledge_tests_reference", feedback.reference));
  }
  content.appendChild(block);
}

function renderKnowledgeTestsFinished(data) {
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  content.innerHTML = "";
  let attempt = data.attempt || {};
  content.appendChild(
    createTagHtml(
      "p",
      "",
      "Результат: " + formatKnowledgeTestsStatus(attempt.status || "") + ", оценка: " + formatKnowledgeTestsScore(attempt.score),
    ),
  );
  if (data.materialsRequired) {
    content.appendChild(createTagHtml("p", "", "Изучите материалы перед повторной попыткой."));
  }
  content.appendChild(
    createKnowledgeTestsActionButton("Закрыть", function () {
      closeKnowledgeTestsOverlay();
    }),
  );
}

function renderKnowledgeTestsMaterials(data) {
  let content = document.getElementById("knowledge_tests_overlay_content");
  if (!content) {
    return;
  }
  content.innerHTML = "";
  (data.items || []).forEach(function (item) {
    content.appendChild(createTagHtml("h3", "", item.questionText || ""));
    content.appendChild(createTagHtml("p", "", item.explanation || ""));
    content.appendChild(createTagHtml("p", "knowledge_tests_reference", item.reference || ""));
  });
  content.appendChild(
    createKnowledgeTestsActionButton("Изучено", function () {
      sendKnowledgeTestsCommand("confirmTestMaterialsExamined", {
        testId: data.testId,
        attemptId: data.attemptId,
      });
    }),
  );
}
