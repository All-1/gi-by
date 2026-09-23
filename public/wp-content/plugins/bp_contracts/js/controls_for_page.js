"use strict";

// CONTROL OVER ELEMENT PER-PAGE ON PAGE
/* START PERPAGE ON PAGE */
function setDefaultState_NEW(where) {
  let whereSearch = 'search_' + where;
  let whereForCase = switchWhere(where);
  const defaultCurrentPerPage = getPerPageSelected();
  const defaultCurrentPage = getCurrentPage();
  const defaultSearchQuery = takeSearchBar(whereSearch);
  socket.send('perPage' + whereForCase + '::: ' + defaultCurrentPerPage);
  socket.send('currentPage' + whereForCase + '::: ' + defaultCurrentPage);
  socket.send('searchQuery' + whereForCase + '::: ' + defaultSearchQuery);
}

function setDefaultStateFilter(where) {
  let whereForCase = switchWhere(where);
  let filter = findElementOnPage('.filters_', where);
  const defaultCheckedNamesNow = checkCheckedFilterNew(filter);
  const jsonData = JSON.stringify(defaultCheckedNamesNow);
  socket.send('setFilter' + whereForCase + '::: ' + jsonData);
}

function setDefaultStateDate(where) {
  let whereForCase = switchWhere(where);
  socket.send('setEndDate' + whereForCase + '::: ' + '');
  socket.send('setStartDate' + whereForCase + '::: ' + '');
}

function sendShowWhere(where) {
  let whereForCase = switchWhere(where);
  console.log('sendShowWhere: ', whereForCase);
  socket.send('show' + whereForCase + '::: ');
}

function getPerPageSelected() {
  let findBoxPerPage = document.querySelector('.perpage-numbers');
  let findPerPageSelected = findBoxPerPage.querySelector('.perpage-number-selected');
  let selectedPerPage = parseInt(findPerPageSelected.innerHTML);
  return selectedPerPage;
}

function controlPerPage(where, send = true) {
  let whereForCase = switchWhere(where);

  let findBoxPerPage = document.querySelector('.perpage-numbers');
  let findPerPageSelected = findBoxPerPage.querySelector('.perpage-number-selected');
  let findPerPageList = findBoxPerPage.querySelector('.perpage-number-list');
  let findPerPageDisable = findPerPageList.querySelectorAll('.perpage-number-disable');
  let findPerPageEnable = findPerPageList.querySelector('.perpage-number-enable');

  findPerPageDisable.forEach(function (perPageDisable) {
    perPageDisable.removeEventListener("click", clickPerPage); // Удаление обработчика
    perPageDisable.addEventListener("click", clickPerPage); // Добавление обработчика
  });

  function clickPerPage(event) {
    let perPageDisable = event.currentTarget;
    findPerPageEnable.classList.add('perpage-number-disable');
    findPerPageEnable.classList.remove('perpage-number-enable');
    perPageDisable.classList.remove('perpage-number-disable');
    perPageDisable.classList.add('perpage-number-enable');
    findPerPageDisable.forEach(function (perPageDisableInner) {
      perPageDisableInner.removeEventListener("click", clickPerPage); // Удаление обработчика после клика
    });
    findPerPageSelected.innerHTML = perPageDisable.innerHTML;
    let currentPerPage = getPerPageSelected();
    if (send) {
      socket.send('perPage' + whereForCase + '::: ' + currentPerPage);
      socket.send('show' + whereForCase + '::: ');
      controlPerPage(where);
    } else {
      changeOnPagePerPage(where, currentPerPage);
      controlPerPage(where, false);
    }
  }
}

function changeOnPagePerPage(where, currentPerPage) {
  perPage = currentPerPage;
  if (where === 'points') {
    prepareUpdatePoints_NEW();
  }
}


/* END PERPAGE ON PAGE */

/* START PAGINATION ON PAGE */
function updatePagination(countPage, currentPage, where, send = true) {
  let whereForCase = switchWhere(where);
  countPage = parseInt(countPage);
  let paginationAdmin = document.getElementById('pagination');
  if (countPage === 1) {
    if (paginationAdmin.children.length > 0) {
      paginationAdmin.innerHTML = '';
    }
  } else {
    if (paginationAdmin.children.length > 0) {
      let paginationPages = paginationAdmin.querySelectorAll('.pagination-page');
      if (paginationPages.length > 1) {
        paginationAdmin.innerHTML = '';
        createNewPagination(countPage, currentPage, whereForCase, send);
      }
    } else {
      createNewPagination(countPage, currentPage, whereForCase, send);
    }
  }
}



function createNewPagination(countPage, currentPage, whereForCase, send) {
  let buttonDeleteContract = document.getElementById('box-before-pagination');
  let findPaginationContracts = document.getElementById('pagination');
  let pagination = findPaginationContracts ? findPaginationContracts : createTagHtml('div', 'pagination-admin', '', 'pagination');
  pagination = packegePagination(currentPage, countPage, pagination);
  buttonDeleteContract.insertAdjacentElement('afterend', pagination);
  let spanPaginationPages = pagination.querySelectorAll('.pagination-page');
  spanPaginationPages.forEach(function (spanPaginationPage) {
    function clickPaginationInner() {
      clickPagination(pagination, spanPaginationPage, whereForCase, send);
    }
    let spanYesCurrent = spanPaginationPage.hasAttribute('id');;
    if (!spanYesCurrent) {
      // Добавляем новый слушатель клика
      spanPaginationPage.addEventListener('click', clickPaginationInner);
    }
  })
}

function changeIdCurrentPage(parrent, currentEllement) {
  let currentPage = parrent.querySelector('#current-page');
  currentPage.removeAttribute('id');
  currentEllement.setAttribute('id', 'current-page');
}

function paginationCallGetData(oldPaginationContracts, whereForCase) {
  let newSpanPaginationPages = oldPaginationContracts.querySelectorAll('.pagination-page');
  newSpanPaginationPages.forEach(function (newSpanPaginationPage) {
    function clickPaginationInner() {
      clickPagination(oldPaginationContracts, newSpanPaginationPage, whereForCase);
    }
    let spanYesCurrent = newSpanPaginationPage.hasAttribute('id');;
    if (!spanYesCurrent) {
      // Добавляем новый слушатель клика
      newSpanPaginationPage.addEventListener('click', clickPaginationInner);
    }
  });
}

function clickPagination(oldPagination, newSpanPaginationPage, whereForCase, send) {
  changeIdCurrentPage(oldPagination, newSpanPaginationPage);
  let newPaginationContracts = oldPagination.cloneNode(true);
  oldPagination.replaceWith(newPaginationContracts);
  paginationCallGetData(newPaginationContracts, whereForCase);
  let currentPage = getCurrentPage();
  if (send) {
    socket.send('currentPage' + whereForCase + '::: ' + currentPage);
    socket.send('show' + whereForCase + '::: ');
  } else {
    changeOnPageCurrentPage(where, currentPage);
  }
};

function changeOnPageCurrentPage(where, page) {
  currentPage = parseInt(page);
  if (where === 'points') {
    prepareUpdatePoints_NEW();
  }
}


function getCurrentPage() {
  let currentPageElement = document.getElementById('current-page');
  let currentPage = currentPageElement ? currentPageElement.getAttribute('data-page-admin') : 1;
  currentPage = parseInt(currentPage);
  return currentPage;
}

function packegePagination(currentPage, countPage, pagination) {
  let minPage = nearestMultipleOfFive(currentPage);
  for (let i = 1; i <= countPage; i++) {
    let paginationSpan;
    if (i < minPage) {
      continue;
    }
    if (isDivisibleByFive(i) && i <= currentPage) {
      let paginationSpanArrowPrev = createTagHtml('span', 'arrow-pagination-prev');
      paginationSpanArrowPrev.setAttribute('data-page-admin', i === 5 ? i - 1 : i - 1);
      pagination = packageArrowPagination(pagination, paginationSpanArrowPrev);
    }
    if (isDivisibleByFive(i) && i > currentPage) {
      let paginationSpanArrowNext = createTagHtml('span', 'arrow-pagination-next', '', '', '', ['page-admin', i]);
      pagination = packageArrowPagination(pagination, paginationSpanArrowNext);
      break;
    }
    if (minPage <= i) {
      paginationSpan = createTagHtml('span', 'pagination-page', i, '', '', ['page-admin', i]);
      pagination.appendChild(paginationSpan);
    }
    if (i === currentPage) {
      paginationSpan.setAttribute('id', 'current-page');
    }
  }
  return pagination;
}

function packageArrowPagination(pagination, paginationSpan) {
  let imgArrowNext = createTagHtml('img');
  imgArrowNext.setAttribute('src', '/wp-content/themes/wp-diary/images/icons/arrow-pagination.svg');
  paginationSpan.classList.add('pagination-page');
  paginationSpan.appendChild(imgArrowNext);
  pagination.appendChild(paginationSpan);
  return pagination;
}
/* END PAGINATION ON PAGE */

/* START SEARCH IN DATABASE */
function startSearch(where) {
  let whereSearch = 'search_' + where;
  let whereForCase = switchWhere(where);
  let searchInput = document.getElementById(whereSearch);
  searchInput.addEventListener('input', startSearchGet);
  function startSearchGet() {
    startSearchSend(whereSearch, whereForCase);
  }
}

function startSearchContracts(where) {
  let whereSearch = 'search_' + where;
  let whereForCase = switchWhere(where);
  let searchInput = document.getElementById(whereSearch);

  searchInput.addEventListener('focus', focusSearchContracts);
  function focusSearchContracts() {
    focusSearch(where, whereForCase);
  }
  searchInput.addEventListener('input', startSearchGet);
  function startSearchGet() {
    startSearchSend(whereSearch, whereForCase);
  }
}

function focusSearch(where, whereForCase) {
  let filters = findElementOnPage('.filters_', where);
  let checkboxFilters = filters.querySelectorAll('input[type="checkbox"]:checked');
  if (checkboxFilters.length !== 0) {
    checkboxFilters.forEach(checkboxFilter => {
      checkboxFilter.checked = false;
    });
    let checkedNamesNow = checkCheckedFilterNew(filters);
    sendFilterOnPage(checkedNamesNow, whereForCase);
  }
}

function startSearchSend(whereSearch, whereForCase) {
  let searchQuery = takeSearchBar(whereSearch);
  // startGetForContract('start', 1);
  socket.send('currentPage' + whereForCase + '::: ' + 1);
  socket.send('setConditionForSearch' + whereForCase + '::: ');
  socket.send('searchQuery' + whereForCase + '::: ' + searchQuery);
  socket.send('show' + whereForCase + '::: ');
}

function takeSearchBar(whereSearch) {
  let searchInput = document.getElementById(whereSearch);
  let searchQuery = searchInput.value.trim();
  searchQuery = searchQuery.replace(/^0+/, '');
  return searchQuery;
}

function startSearchInvoices(where) {
  let whatCondition = window.location.search.split('=')[0];
  let searchQuery = window.location.search.split('=')[1];
  if (searchQuery) {
    let whereSearch = 'search_' + where;
    let searchInput = document.getElementById(whereSearch);
    searchInput.value = searchQuery;
    let whereForCase = switchWhere(where);
    socket.send('setConditionForSearch' + whereForCase + '::: ' + 'invoices');
    socket.send('currentPage' + whereForCase + '::: ' + 1);
    socket.send('searchQuery' + whereForCase + '::: ' + searchQuery);
    // socket.send('show' + whereForCase + '::: ');
  }
}

function responseNothingFound(where) {
  let targetContractOnPages = document.querySelectorAll('[data-sn-contract-table');
  targetContractOnPages.forEach(function (targetContractOnPage) {
    targetContractOnPage.remove();
  });
  let headerContracts = document.getElementById('tr_header_' + where);
  let nothingFound = document.getElementById('nothing_found');
  if (!nothingFound) {
    nothingFound = createTagHtml("div", "nothing_found", 'Мы очень старались, всё обыскали, под каждым камнем посмотрели, но такого в нашей базе нет.', 'nothing_found');
    headerContracts.insertAdjacentElement('afterend', nothingFound);
  }
  let paginationContracts = document.getElementById('pagination-contracts');
  if (paginationContracts) {
    paginationContracts.innerHTML = '';
  }
}
/* END SEARCH IN DATABASE */

/* START FILTER ON PAGE */
function filterOnPage(where) {
  let whereForCase = switchWhere(where);

  let filterContracts = findElementOnPage('.filters_', where);
  let checkboxFilters = filterContracts.querySelectorAll('input[type="checkbox"]');
  checkboxFilters.forEach(function (checkboxFilter) {
    checkboxFilter.addEventListener('click', callFilterCallGetData);
  })
  function callFilterCallGetData() {
    let checkedNamesNow = checkCheckedFilterNew(filterContracts);
    sendFilterOnPage(checkedNamesNow, whereForCase);
  }
}

function sendFilterOnPage(checkedNamesNow, whereForCase) {
  let jsonData = JSON.stringify(checkedNamesNow);
  socket.send('currentPage' + whereForCase + '::: ' + 1);
  socket.send('setFilter' + whereForCase + '::: ' + jsonData);
  socket.send('show' + whereForCase + '::: ');
}

function findElementOnPage(what, where) {
  let whatFind = what + where;
  let element = document.querySelector(whatFind);
  return element;
}

function checkCheckedFilterNew(filterContracts) {
  let filtersChecked = filterContracts.querySelectorAll('input[type="checkbox"]:checked');
  let checkedNamesNow = Array.from(filtersChecked).map(function (checkbox) {
    return checkbox.getAttribute('name');
  });
  if (checkedNamesNow.length > 0) {
    return checkedNamesNow;
  } else {
    return [];
  }
}

function checkCheckedFilterNew_2(filterContracts, type) {
  let filtersChecked = filterContracts.querySelectorAll('input[type="' + type + '"]:checked');
  let checkedNamesNow = Array.from(filtersChecked).map(function (radio) {
    return radio.getAttribute('id');
  });
  if (checkedNamesNow.length > 0) {
    return checkedNamesNow;
  } else {
    return [];
  }
}

/* END FILTER ON PAGE */

/* START WORK WITH DATE */
function filterDate(where) {
  let whereForCase = switchWhere(where);

  let startDateInput = document.getElementById('start_date_' + where);
  let endDateInput = document.getElementById('end_date_' + where);
  startDateInput.addEventListener('change', function () {
    callEventForDate(startDateInput, 'StartDate', whereForCase);
  });
  endDateInput.addEventListener('change', function () {
    callEventForDate(endDateInput, 'EndDate', whereForCase);
  });
}

function callEventForDate(dateInput, property, whereForCase) {
  let date = dateInput.value;
  let jsonData = JSON.stringify(date);
  socket.send('currentPage' + whereForCase + '::: ' + 1);
  socket.send('set' + property + whereForCase + '::: ' + jsonData);
  socket.send('show' + whereForCase + '::: ');
}
/* END WORK WITH DATE */

/* START WORK WITH BUTTON ON PAGE */
function callListenerButtonOLD(where, buttonID) {
  let button = document.getElementById(buttonID + where);
  button.addEventListener('click', function () {
    listenerCheckboxesOLD(where, buttonID);
  });

}

function listenerCheckboxesOLD(where, buttonID) {
  let whereForCase = switchWhere(where);
  let socketCommand = switchCommand(buttonID) + whereForCase;
  let checkboxesChecked = document.querySelectorAll(".checkbox_" + where + ":checked");
  let numbers = [];
  checkboxesChecked.forEach(element => {
    let number = element.value;
    numbers.push(number);
  });
  let jsonData = JSON.stringify(numbers);
  socket.send(socketCommand + '::: ' + jsonData);
}

function callListenerButton(where, buttonID, who = null) {
  let button = document.getElementById(buttonID + where);
  button.addEventListener('click', function () {
    listenerCheckboxes(where, buttonID, who);
  });
}

function listenerCheckboxes(where, buttonID, who) {
  let whereForCase = switchWhere(where);
  let socketCommand = switchCommand(buttonID) + whereForCase;
  let idWho = checkWhoExist(who, where);
  let block = checkExistBlock(idWho, where);
  let checkboxesChecked = block.querySelectorAll(".checkbox_" + where + ":checked");
  let data = createPrototypeForData(idWho, 'idUser', 'idPoints');
  data = packageData(data, checkboxesChecked, 'idPoints');

  let jsonData = JSON.stringify(data);

  socket.send(socketCommand + '::: ' + jsonData);
}

function packageData(data, checkboxes, param) {
  checkboxes.forEach(element => {
    let id = element.value;
    if (data[param]) {
      data[param].push(id);
    } else {
      data.push(id);
    }
  });
  delete data[0];
  return data;
}

function checkWhoExist(who, where) {
  if (who) {
    let inputWho = document.querySelector(".choice_" + where + '_' + who + ":checked");
    let idWho = inputWho.getAttribute('data-id-' + who);
    return idWho;
  }
  return null;
}

function checkExistBlock(id) {
  if (id) {
    let block = document.querySelector('[data-id-block-' + where + '="' + id + '"]');
    return block;
  }
  return document;
}

function createPrototypeForData(id, firstName, secondName) {
  if (id) {
    let data = {};
    data[firstName] = id;
    data[secondName] = [];
    return data;
  }
  return [];
}

/* END WORK WITH BUTTON ON PAGE */

/*START PAINT ROW IN TABLE*/
function paintBackgroundRowInTable(where) {
  let checkboxes = document.querySelectorAll('.checkbox_' + where);
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function () {
      changeBackgroundRowContract(where, checkbox);
    });
  })
}
function changeBackgroundRowContract(where, checkbox) {
  let row = checkbox.closest('.tr_div_table_' + where);
  if (checkbox.checked) {
    row.style.backgroundColor = '#DFE6F9';
  } else {
    row.style.backgroundColor = '#fff';
  }
}
/*END PAINT ROW IN TABLE*/

/*START CONTROL CHOICE DROP-LIST*/
function checkStatusInputManager(inputManger) {
  if (inputManger.checked === true) {
    let inputsManager = document.querySelectorAll('.choice_points_manager');
    inputsManager.forEach(input => {
      let idManager = input.getAttribute('data-id-manager');
      let blockPoints = document.querySelector('[data-id-block-points="' + idManager + '"]');
      if (input !== inputManger) {
        input.checked = false;
        blockPoints.style.display = "none";
      } else {
        blockPoints.style.display = "block";
      }
    })
  }
}

/*END CONTROL CHOICE DROP-LIST*/

/*START CONTROL CHOICE DROP-LIST radio-button*/
function filterRadio(where, nameFilter) {
  let tabs = document.querySelector('.tabs-' + where);
  let checkboxFilters = tabs.querySelectorAll('input[type="radio"]');
  let whereForCase = switchWhere(where);
  checkboxFilters.forEach(radio => {
    radio.addEventListener('change', function () {
      if (radio.checked === true) {
        socket.send('currentPage' + whereForCase + '::: ' + 1);
        sendFilter(where, nameFilter, radio.id);
      }
    });
  });

}
/*END CONTROL CHOICE DROP-LIST radio-button*/

/*START CONTROL SELECT SORT*/
function filterSelect(where, nameFilter) {
  let select = document.querySelector('.filter-' + where);
  let headerSelect = select.querySelector('.header-filters-' + where);
  let dropDownList = select.querySelector('.list-filters-' + where);
  let elementsList = dropDownList.querySelectorAll('li');

  elementsList.forEach(element => {
    element.addEventListener('click', function () {
      let whereForCase = switchWhere(where);
      socket.send('currentPage' + whereForCase + '::: ' + 1);
      sendFilter(where, nameFilter, element.id);
      changeStyleSelect(nameFilter, element, headerSelect, select);
    })
  })
}



function changeStyleSelect(nameFilter, element, headerSelect, select) {
  let selectedBefore = select.querySelector('.selected');
  headerSelect.innerHTML = element.innerHTML;
  element.classList.add('selected');
  if (selectedBefore) {
    selectedBefore.classList.remove('selected');
  }
  let resetSelect = createTagHtml('div', 'reset-select', '', 'null');
  headerSelect.appendChild(resetSelect);
  resetSelect.addEventListener('click', function () {
    let whereForCase = switchWhere(where);
    socket.send('currentPage' + whereForCase + '::: ' + 1);
    sendFilter(where, nameFilter, resetSelect.id);
    headerSelect.innerHTML = '-';
  });

}
/*END CONTROL SELECT SORT*/

/*UNIVERSAL SEND FUNCTION FILTERS ON PAGE*/
//Replace all send function on this
function sendFilter(where, nameFilter, data) {
  let whereForCase = switchWhere(where);
  let jsonData = JSON.stringify(data);
  console.log('sendFilter: ', nameFilter + whereForCase + '::: ' + jsonData);
  socket.send(nameFilter + whereForCase + '::: ' + jsonData);
  socket.send('show' + whereForCase + '::: ');
}
/*UNIVERSAL SEND FUNCTION FILTERS ON PAGE*/


function clickAllCheckBox(where) {
  let allCheck = document.getElementById("all_check_" + where);
  if (!allCheck) {
    return; // Проверка наличия элемента
  }
  allCheck.addEventListener('click', function (event) {
    let checkboxes = document.querySelectorAll('.checkbox_' + where);
    const targetChecked = event.currentTarget.checked;
    checkboxes.forEach(input => {
      if (input.checked !== targetChecked) {
        input.checked = targetChecked;
        // Программное изменение checked не генерирует событие — сгенерируем его вручную
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });
}

function fixedHeaderTable(where) {
  const headerTable = document.getElementById('tr_header_' + where);
  const sticky = headerTable.offsetTop;
  window.addEventListener('scroll', function () {
    if (window.scrollY > sticky) {
      headerTable.classList.add('fixed-header-table');
    } else {
      headerTable.classList.remove('fixed-header-table');
    }
  });

}

function fixedHeaderTablePoints(where) {
  const headerTable = document.getElementById('tr_header_' + where);
  const outerContainer = headerTable.parentElement;
  const sticky = headerTable.offsetTop;
  window.addEventListener('scroll', function () {
    if (window.scrollY > sticky) {
      const container = document.querySelector('.table-' + where);
      const x = container.scrollLeft;
      outerContainer.classList.add('fixed-header-table');
      headerTable.style.marginLeft = `${-x}px`;
      scrollContainer(where);
    } else {
      outerContainer.classList.remove('fixed-header-table');
      headerTable.style.marginLeft = `0px`;
    }
  });

}

function scrollContainer(where) {
  const scrollContainer = document.querySelector('.table-' + where);
  const headerTable = document.getElementById('tr_header_' + where);
  scrollContainer.addEventListener('scroll', function () {
    let windowWidth = window.innerWidth;
    let widthHeaderTable = headerTable.offsetWidth;
    let difference = (windowWidth - 1110) / 2;
    let left = scrollContainer.scrollLeft;
    headerTable.style.marginLeft = `${-left}px`;
  });
}


