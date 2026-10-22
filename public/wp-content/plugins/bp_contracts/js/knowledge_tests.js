"use strict";

/**
 * Knowledge tests — personal account page (same pattern as orders.js / analytics.js).
 * WS commands: showTests, startTest, … — responses: handlerTestsOnPage, …
 */

const where = "knowledge_tests";

let knowledgeTestsPage = 1;
let knowledgeTestsPerPage = 20;
let knowledgeTestsOverlayTestId = null;

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
  tests.forEach(function (row) {
    container.appendChild(buildKnowledgeTestsListRow(row));
  });
  renderKnowledgeTestsPagination(data.pagination || {});
}

function buildKnowledgeTestsListRow(row) {
  let line = createTagHtml("div", "tr_div_knowledge_tests");
  let title = createTagHtml("div", "td_div_knowledge_tests kt-col-title", row.title || "");
  let status = createTagHtml(
    "div",
    "td_div_knowledge_tests kt-col-status",
    formatKnowledgeTestsStatus(row.displayStatus),
  );
  let scoreText = row.score != null ? String(row.score) : "—";
  let score = createTagHtml("div", "td_div_knowledge_tests kt-col-score", scoreText);
  let actions = createTagHtml("div", "td_div_knowledge_tests kt-col-actions");
  if (row.displayStatus === "in_progress" || row.canStart) {
    let startLabel = row.displayStatus === "in_progress" ? "Resume" : "Start";
    actions.appendChild(
      createKnowledgeTestsActionButton(startLabel, function () {
        openKnowledgeTestsOverlay(row.testId, row.title, "startTest");
      }),
    );
  }
  if (row.canOpenMaterials) {
    actions.appendChild(
      createKnowledgeTestsActionButton("Materials", function () {
        openKnowledgeTestsOverlay(row.testId, row.title, "getTestMaterials");
      }),
    );
  }
  massAppendChild(line, title, status, score, actions);
  return line;
}

function createKnowledgeTestsActionButton(label, onClick) {
  let btn = createTagHtml("button", "knowledge_tests_action_btn", label, "", "button");
  btn.addEventListener("click", onClick);
  return btn;
}

function formatKnowledgeTestsStatus(code) {
  let map = {
    not_started: "Not started",
    in_progress: "In progress",
    failed: "Failed",
    passed: "Passed",
    outdated: "Outdated",
  };
  return map[code] || code || "—";
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
  let info = createTagHtml("span", "knowledge_tests_page_info", "Page " + page + " / " + pages);
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
  content.appendChild(createTagHtml("p", "knowledge_tests_loading", "Loading…"));
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
    return "Request failed.";
  }
  let reasons = data.error.reasons || [];
  return data.error.code + (reasons.length ? ": " + reasons.join(", ") : "");
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
      "Question " + ((progress.index || 0) + 1) + " / " + (progress.total || "—"),
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
  let continueBtn = createKnowledgeTestsActionButton("Continue", function () {
    submitKnowledgeTestsQuestion(data.testId, form);
  });
  content.appendChild(continueBtn);
  if (data.feedback && data.feedback.show) {
    renderKnowledgeTestsFeedback(content, data.feedback);
  }
  if (data.sessionComplete) {
    content.appendChild(
      createKnowledgeTestsActionButton("Finish test", function () {
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
    createTagHtml("p", "", "Status: " + (attempt.status || "") + ", score: " + (attempt.score ?? "—")),
  );
  if (data.materialsRequired) {
    content.appendChild(createTagHtml("p", "", "Review materials before retake."));
  }
  content.appendChild(
    createKnowledgeTestsActionButton("Close", function () {
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
    createKnowledgeTestsActionButton("Examined", function () {
      sendKnowledgeTestsCommand("confirmTestMaterialsExamined", {
        testId: data.testId,
        attemptId: data.attemptId,
      });
    }),
  );
}
