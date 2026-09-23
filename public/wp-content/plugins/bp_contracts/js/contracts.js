
/*

*/
"use strict";

/* СОЗДАНИЕ НОВОГО КОНТРАКТА*/
let genContractButton = document.getElementById("gen-contracts");
if (genContractButton) {
  genContractButton.addEventListener("click", clickGenContractButton);
}
const where = 'contracts';
controlPerPage(where);
startSearchContracts(where);
filterOnPage(where);
callListenerButtonOLD(where, 'delete-empty-');
clickAllCheckBox(where);
fixedHeaderTable(where);


function setDefaultState(where) {
  if (socket.readyState === WebSocket.OPEN) {
    setDefaultState_NEW(where);
    setDefaultStateFilter(where);
    sendShowWhere(where);
  }
}

function handlerContractOnPage(data, user) {
  let userParam = user.getAllParam();
  contractsOnPage(data, userParam);
}

function handlerSendDataForContract(data, user) {
  let userParam = user.getAllParam();
  contractForWindow(data, userParam);
}

function clickGenContractButton() {
  let inputNewContract = document.getElementById("search_contracts");
  let inputIdPoint = document.querySelector('input[name="point_id"]');
  if (inputNewContract.value) {
    let whereSearch = 'search_' + where;
    let whereForCase = toCapsCase(where);
    let formData = {
      "new_contract": inputNewContract.value,
      "point_id": inputIdPoint.value
    }
    inputNewContract.value = '';
    let jsonData = JSON.stringify(formData);
    socket.send('newContract::: ' + jsonData);
    startSearchSend(whereSearch, whereForCase);
  }
}

function contractsOnPage(data, user) {
  try {
    // Парсинг JSON-строки в объект
    // data = JSON.parse(data);
    let jsonData = data.reverse();
    let flagUpdate = checkUpdateTable(jsonData, where);
    let countPage;
    jsonData.forEach(element => {
      element.serialNumber = parseInt(element.serialNumber);
      if (jsonData.length === 1) {
        responseNothingFound(where); //Вывели ответ что ничего нет в базе
        return;
      }
      if (element.countPage) {
        countPage = element.countPage;
        return;
      }
      updateContractsTable(element, flagUpdate, user);
    });
    let currentPage = getCurrentPage();
    updatePagination(countPage, currentPage, where);
    paintBackgroundRowInTable(where);
  } catch (error) {
    console.error('Ошибка при парсинге JSON:', error);
  }
}


/* A LITTLE BIT LATER I WILL WRITE DESCRIPTION FOR THIS CODE */
function updateContractsTable(data, flagUpdate, user) {
  let targetContractOnPage = document.querySelector('[data-sn-contract-table="' + data.serialNumber + '"]');
  let nothingFound = document.getElementById('nothing_found');
  if (nothingFound) {
    nothingFound.remove();
  }
  if (!targetContractOnPage) {
    showNewContractData(data, user);
  } else if (flagUpdate) {
    updateContractRow(data, targetContractOnPage, user);
    targetContractOnPage.parentNode.removeChild(targetContractOnPage);
    let headerContracts = document.querySelector('#tr_header_contracts');
    headerContracts.insertAdjacentElement("afterend", targetContractOnPage);
  } else {
    updateContractRow(data, targetContractOnPage, user);
  }
}

function updateContractRow(data, parent, user) {
  let headerContracts = document.querySelector('#tr_header_contracts');
  let contractDate = parent.querySelector('.contracts_date');
  let contractCheckbox = parent.querySelector('.checkbox_contracts');
  let prevDateLastActivity = parseInt(contractDate.getAttribute('data-date-last-activity'));
  let currentDateLastActivity = parseInt(data.dateLastActivityTimestamp);
  let contractDialogLink = parent.querySelector('.contract_dialog_link');
  // let checkBoxContainer = parent.querySelector('.contracts_checkbox');
  let currentContractName = contractDialogLink.textContent;

  if (data.nameContract !== currentContractName) {
    contractDialogLink.textContent = data.nameContract;
  }

  if (data.dateCreationTimestamp < data.dateLastActivityTimestamp) {
    if (contractCheckbox) {
      contractCheckbox.remove();
      parent.style.backgroundColor = '#fff';
    }
  }
  //let now = (Date.now())/1000 + (60*60*3);

  if (currentDateLastActivity > prevDateLastActivity) {
    contractDate.setAttribute('data-date-last-activity', data.dateLastActivityTimestamp);
    parent.parentNode.removeChild(parent);
    headerContracts.insertAdjacentElement("afterend", parent);
  }

  let boxDateLastActivity = contractDate.querySelector('.date-last-activity');
  boxDateLastActivity.innerHTML = '';
  boxDateLastActivity.innerHTML = getTimeAgo(currentDateLastActivity);
  let markerUnreadConsultationHTML = contractDate.querySelector('.type_activity_consultation');
  let markerUnreadOrderHTML = contractDate.querySelector('.type_activity_order');
  let markerUnreadComplaintHTML = contractDate.querySelector('.type_activity_complaint');
  let hiddenBoxDateLastActivity = createTagHtml("div", "hidden-date-last-activity", formatTimestamp(currentDateLastActivity));
  boxDateLastActivity.appendChild(hiddenBoxDateLastActivity);

  if (data.markerUnreadConsultation && !markerUnreadConsultationHTML) {
    markerUnreadConsultationHTML = createTagHtml('span', 'type_activity_consultation', 'К');
    contractDate.appendChild(markerUnreadConsultationHTML);
  }
  if (data.markerUnreadOrder && !markerUnreadOrderHTML) {
    markerUnreadOrderHTML = createTagHtml('span', 'type_activity_order', 'З');
    contractDate.appendChild(markerUnreadOrderHTML);
  }
  if (data.markerUnreadComplaint && !markerUnreadComplaintHTML) {
    markerUnreadComplaintHTML = createTagHtml('span', 'type_activity_complaint', 'Р');
    contractDate.appendChild(markerUnreadComplaintHTML);
  }
  if (!data.markerUnreadConsultation && markerUnreadConsultationHTML) {
    markerUnreadConsultationHTML.remove();
  }
  if (!data.markerUnreadOrder && markerUnreadOrderHTML) {
    markerUnreadOrderHTML.remove();
  }
  if (!data.markerUnreadComplaint && markerUnreadComplaintHTML) {
    markerUnreadComplaintHTML.remove();
  }
  if (data.dateCreationTimestamp < data.dateLastActivityTimestamp) {
    if (contractCheckbox) {
      contractCheckbox.remove();
    }
  }
}

function showNewContractData(data, user) {
  let trHeaderContracts = document.getElementById("tr_header_contracts");
  let trContract = createTagHtml("div", "tr_div_table_contracts", '', '', '', ['sn-contract-table', data.serialNumber]);
  let snForUser = String(data.serialNumber).padStart(6, "0");

  let tdContractCheckbox = createTagHtml("div", "td_div_table_contracts");
  let tdContractNumber = createTagHtml("div", "td_div_table_contracts", snForUser);
  let tdContractName = createTagHtml("div", "td_div_table_contracts");
  let tdContractPointName = createTagHtml("div", "td_div_table_contracts", data.pointName);
  let tdContractDate = createTagHtml("div", "td_div_table_contracts", '', '', '', ['date-last-activity', data.dateLastActivityTimestamp]);
  tdContractDate.classList.add('contracts_date');
  let boxDateLastActivity = createTagHtml("div", "date-last-activity", getTimeAgo(data.dateLastActivityTimestamp));
  let currentDateLastActivity = parseInt(data.dateLastActivityTimestamp);
  let hiddenBoxDateLastActivity = createTagHtml("div", "hidden-date-last-activity", formatTimestamp(currentDateLastActivity));
  tdContractDate.appendChild(boxDateLastActivity);
  boxDateLastActivity.appendChild(hiddenBoxDateLastActivity);

  let checkboxContract = createTagHtml("input", "checkbox_contracts", "", "contract-" + data.serialNumber, "checkbox");

  let contractLink = data.nameContract
    ? createTagHtml("a", "contract_dialog_link", data.nameContract)
    : createTagHtml("a", "contract_dialog_link", snForUser);
  checkboxContract.setAttribute("data-contract-checkbox-sn", data.serialNumber);
  checkboxContract.value = data.serialNumber;
  contractLink.setAttribute("data-contract-link-sn", data.serialNumber);
  if (data.markerUnreadConsultation) {
    let markerUnreadConsultationHTML = createTagHtml('span', 'type_activity_consultation', 'К');
    tdContractDate.appendChild(markerUnreadConsultationHTML);
  }
  if (data.markerUnreadOrder) {
    let markerUnreadOrderHTML = createTagHtml('span', 'type_activity_order', 'З');
    tdContractDate.appendChild(markerUnreadOrderHTML);
  }
  if (data.markerUnreadComplaint) {
    let markerUnreadComplaintHTML = createTagHtml('span', 'type_activity_complaint', 'Р');
    tdContractDate.appendChild(markerUnreadComplaintHTML);
  }

  if (data.dateCreationTimestamp === data.dateLastActivityTimestamp) {
    tdContractCheckbox.appendChild(checkboxContract);
  }
  massAppendChild(
    trContract,
    tdContractCheckbox, tdContractNumber, tdContractName, tdContractPointName, tdContractDate
  );

  tdContractName.appendChild(contractLink);
  trHeaderContracts.insertAdjacentElement("afterend", trContract);
  eventListenerCallContractWindow(data, contractLink, user, 'contracts');
}
