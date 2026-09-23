const where = 'contractors';

startSearch(where);
controlPerPage(where);
// filterOnPage(where);
filterRadio(where, 'setFilterRole');
filterSelect(where, 'setFilterStatus');
// setTimeout(() => {
//   setDefaultState_NEW(where);
//   setDefaultStatePartner(where);
// }, 1000);

function setDefaultState(where) {
  if (socket.readyState === WebSocket.OPEN) {
    setDefaultState_NEW(where);
    setDefaultStatePartner(where);
  }
}

function setDefaultStatePartner(where) {
  let whereForCase = toCapsCase(where);
  let filter = findElementOnPage('.tabs-', where);
  const defaultCheckedNamesNow = checkCheckedFilterNew_2(filter, 'radio');
  socket.send('setFilterStatus' + whereForCase + '::: ' + JSON.stringify(String(null)));
  sendFilter(where, 'setFilterRole', String(defaultCheckedNamesNow));
}

function handlerContractorsOnPage(data, user) {
  let userParam = user.getAllParam();
  console.log('data', data);
  const reverseArray = data.reverse();
  contractorsOnPage(reverseArray, userParam);
}

function handlerShowForBindContractors(data) {
  showForBindContractors(data);
}

function contractorsOnPage(data, user) {
  let flagUpdate = checkUpdateTable(data, where);
  let countPage;
  data.forEach(contractor => {
    if (data.length === 1) {
      responseNothingFound(where); //Вывели ответ что ничего нет в базе
      return;
    }
    if (contractor.countPage) {
      countPage = contractor.countPage;
      return;
    }
    updateContractrorsTable(contractor, user);
  });
  let currentPage = getCurrentPage();
  updatePagination(countPage, currentPage, where);
}

function updateContractrorsTable(data, user) {
  let headerContractors = document.getElementById('tr_header_contractors');
  let nothingFound = document.getElementById('nothing_found');
  if (nothingFound) {
    nothingFound.remove();
  }
  let trContractor = showNewContractorData(data, user);
  headerContractors.insertAdjacentElement("afterend", trContractor);

}

function isBindingVisible(data) {
  return data.binded === 'bind' && data.status !== 'blocked';
}

function showNewContractorData(data, user) {
  let trContractor = createTagHtml("div", "row-table-contractors", '', '', '', ['id-user-contractors', data.idUser]);

  let boxCheckbox = createTagHtml("div", "box-checkbox-contractors");
  let checkboxContractor = createTagHtml("input", "checkbox-contractors", "", "", "checkbox");
  checkboxContractor.setAttribute("data-checkbox-contractor", data.idUser);

  let boxName = createTagHtml('div', 'name-contractors');
  boxName = createBoxName(data, boxName);

  let status = createTagHtml('div', 'status-contractors');
  status = createStatusUser(status, data);

  let bindTo = createTagHtml('div', 'bind-to');
  if (isBindingVisible(data)) {
    let textBindTo = createTagHtml('div', 'bind-to-text', data.bindTo);
    let unbind = createTagHtml('div', 'unbind');
    massAppendChild(
      bindTo,
      textBindTo, unbind
    );
    unbind.addEventListener('click', function () {
      let sendData = { 'userId': data.idUser }
      sendFilter(where, 'unbind', sendData);
      console.log('unbind', sendData);
    });
  }
  let menu = createTagHtml('div', 'menu-control-user');

  boxCheckbox.appendChild(checkboxContractor);

  massAppendChild(
    trContractor,
    boxCheckbox, boxName, status, bindTo
  );

  return trContractor;
}


function createBoxName(data, boxName) {
  boxName.innerHTML = '';
  let nameContractor = createTagHtml('div', 'text-name-contractor', data.lastname + ' ' + data.name);
  let redactName = createTagHtml('div', 'redact-name-contractors');
  let boxAdditionalInfo = createTagHtml('div', 'user-additional-info');
  let userInfo = createTagHtml('div', 'user-info');
  let userInfoHidden = createUserInfo(data);
  
  boxAdditionalInfo.appendChild(userInfo);
  massAppendChild(
    boxName,
    nameContractor, redactName, boxAdditionalInfo
  );
  
  userInfo.addEventListener('mouseenter', function() {
    boxAdditionalInfo.appendChild(userInfoHidden);

  });
  redactName.addEventListener('click', function () {
    clickRedactName(data, boxName);
  });
  return boxName;
}

function createUserInfo(data) {
  // createRowInfo(data, 'nameWP');
  let mainBoxInfo = createTagHtml('div', 'main-user-info-box');
  let userId = createRowInfo(data, 'idUser', 'ID в WP');
  let nameWP = createRowInfo(data, 'nameWP', 'Имя в WP');
  // let nameUP = data.nameUP ? createRowInfo(data, 'nameUP', 'Имя в UP') : '';
  // let statusRus = createRowInfo(data, 'statusRus', 'Статус');
  let myDistributorNameUP = isBindingVisible(data) && data.myDistributorNameUP ? createRowInfo(data, 'myDistributorNameUP', 'Имя дистрибьютора') : '';
  let myDealerNameUP = isBindingVisible(data) && data.myDealerNameUP && data.role === 'designer_dealer' ? createRowInfo(data, 'myDealerNameUP', 'Имя дилера') : '';
  let country = createRowInfo(data, 'country', 'Страна');
  let city = createRowInfo(data, 'city', 'Город');
  let address = createRowInfo(data, 'address', 'Адрес');
  let phone = createRowInfo(data, 'phone', 'Телефон');
  let company = createRowInfo(data, 'company', 'Компания');
  
  massAppendChild(
    mainBoxInfo,
    userId, nameWP, myDistributorNameUP,
    myDealerNameUP, country, city, address, 
    phone, company
  );
  return mainBoxInfo;
}

function createRowInfo(data, property, title) {
  let box = createTagHtml('div', 'user-info-box-row');

  Object.keys(data).forEach(function (key) {
    if (key === property) {
      let titleBox = createTagHtml('div', 'titke-row-info-box', title + " : ");
      let propertyBox = createTagHtml('div', property, data[property]);
      massAppendChild(
        box,
        titleBox, propertyBox
      );
    }
  });
  return box;
}

function clickRedactName(data, boxName) {
  boxName.innerHTML = '';
  let inputName = createTagHtml('input', 'input-name-contractor', '', '', 'text');
  let inputLastname = createTagHtml('input', 'input-name-contractor', '', '', 'text',);
  let cancelChanges = createTagHtml('div', 'cancel-changes-name');
  let acceptChanges = createTagHtml('div', 'accept-changes-name');
  
  inputName.value = data.name;
  inputName.name = 'name';
  inputName.placeholder = 'Имя';
  inputLastname.value = data.lastname;
  inputLastname.name = 'lastname';
  inputLastname.placeholder = 'Фамилия';

  massAppendChild(
    boxName,
    inputLastname, inputName, acceptChanges, cancelChanges
  );
  inputLastname.focus();
  cancelChanges.addEventListener('click', function () {
    boxName = createBoxName(data, boxName);
  });
  acceptChanges.addEventListener('click', function () {
    let sendData = {
      'userId': data.idUser,
      'name': inputName.value,
      'lastname': inputLastname.value,
    };
    sendFilter(where, 'changeUserName', sendData);
  });
}

function createStatusUser(status, data) {

  let boxStatusControl = createTagHtml('div', 'box-status-control');
  let statusText = createTagHtml('div', 'status-text', data.statusRus);
  massAppendChild(status,
    boxStatusControl, statusText
  );

  if (data.binded === 'bind') {
    let inputStatus = createTagHtml('input', 'status-contractor', '', 'status-contractor-' + data.idUser, "checkbox");
    inputStatus.value = data.status;

    let labelStatus = createTagHtml('label', 'label-status');
    let circle = createTagHtml('div', 'circle');
    labelStatus.appendChild(circle);
    labelStatus.setAttribute('for', 'status-contractor-' + data.idUser);

    massAppendChild(boxStatusControl,
      inputStatus, labelStatus
    );

    if (data.status !== 'blocked') {
      inputStatus.checked = true;
    } else {
      inputStatus.checked = false;
    }

    inputStatus.addEventListener('change', function () {
      let status = inputStatus.checked ? 'active' : 'blocked';
      let sendData = {
        'statusActivity': status,
        'userId': data.idUser,
      }
      sendFilter(where, 'changeStatus', sendData);
    });
  } else {
    let divNobind = createTagHtml('div', 'status-contractor-nobind');
    status.classList.add('status-nobind');
    boxStatusControl.appendChild(divNobind);
    controlUnbindedUser(status, data);
  }
  return status;
}

function controlUnbindedUser(status, data) {
  status.addEventListener('click', clickStatus);
  function clickStatus() {
    addSearchInput(status, data);
    status.removeEventListener("click", clickStatus);
  }
}

function addSearchInput(status, data) {
  status.innerHTML = '';
  status.classList.add('status-bind-now');
  let searchBind = createTagHtml("input", "search_bind", "", "", "search");
  let cancelBind = createTagHtml('div', 'cancel-bind');
  let acceptBind = createTagHtml('div', 'accept-bind');
  searchBind.placeholder = data.role === 'designer_dealer' ? 'Имя точки' : 'Имя диллера/дистрибьютера УП';
  massAppendChild(
    status,
    searchBind, acceptBind, cancelBind
  );
  searchBind.focus();
  cancelBind.addEventListener('click', function () {
    status.innerHTML = '';
    status.classList.remove('status-bind-now');
    setTimeout(() => {
      createStatusUser(status, data);
    }, 0);
  });
  searchBind.addEventListener('input', function () {
    startSearchBind(searchBind, data);
  });
  acceptBind.addEventListener('click', function () {
    let sendData = prepareDataBind(data, searchBind);
    if (sendData) {
      sendFilter(where, 'bind', sendData);
    }
  });
}

function prepareDataBind(data, inputSearch) {
  let id = inputSearch.getAttribute('data-id-up') ? inputSearch.getAttribute('data-id-up') : null;
  if (id) {
    let newData = { 'id': id, 'userId': data.idUser };
    return newData;
  }
  return null;
}

function startSearchBind(searchInput, data) {
  let searchQuery = searchInput.value.trim();
  searchQuery = searchQuery.replace(/^0+/, '');
  let newData = {
    'idUser': data.idUser,
    'searchQuery': searchQuery,
  }
  let jsonData = JSON.stringify(newData);
  socket.send('searchForBindContractors' + '::: ' + jsonData);
}

function showForBindContractors(data) {

  if (data) {
    let findRowContractor = document.querySelector('[data-id-user-contractors="' + data.idUser + '"]');
    let findStatusBox = findRowContractor.querySelector('.status-contractors');
    let divResult = createResultSearch(data, findStatusBox);

    findStatusBox.appendChild(divResult);
  }
}

function createResultSearch(data, container) {
  let searchBox = container.querySelector('.search_bind');
  let findDivResult = container.querySelector('.result-search-contractors');
  let dataArr = Object.values(data);
  let divResult = findDivResult ? findDivResult : createTagHtml('div', 'result-search-contractors');
  divResult.innerHTML = '';

  Object.keys(data).forEach(item => {
    let newData;
    if (dataArr.length > 1) {
      let name = data[item].namePoint ? data[item].namePoint : data[item].nameDealer;
      let id = data[item].idPoint ? data[item].idPoint : data[item].idDealer;
      newData = { 'name': name, 'id': id, 'idUser': data[item] }
    }
    if (dataArr.length > 2 && item !== 'idUser') {
      let rowResult = createTagHtml('div', 'row-result-search-contractors', newData.name);
      divResult.appendChild(rowResult);
      rowResult.addEventListener('click', function () {
        clickRowResult(searchBox, newData, divResult);
      });
    } else if (dataArr.length > 1 && item !== 'idUser') {
      clickRowResult(searchBox, newData, divResult);
      divResult.remove();
    } else {
      divResult.remove();
    }
  });
  return divResult;
}

function clickRowResult(searchBox, data, divResult) {
  searchBox.setAttribute('data-id-up', data.id);
  searchBox.value = data.name;
  divResult.remove();
}