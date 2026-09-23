"use strict";
const where = 'points';
const dataPoints = [];
let userParam = null;
let allPoints = null;
let preparedPoints = null;
let temporaryPoints = [];
let filtredPoints = [];
let perPage = 20;
let currentPage = 1;
let allManagers = [];
let managersTemporary = [];
let hideManagers = [];
let changeTableMarker = false;

function saveDataPoints() {
  let saveButton = document.querySelector('#save-points');
  if (saveButton) {
    saveButton.addEventListener('click', function () {
      if (dataPoints.length > 0) {
        simpleSendData(dataPoints, 'savePoints');
      }
    });
  }
}

saveDataPoints();
fixedHeaderPointsTable();

function fixedHeaderPointsTable() {
  const headerTable = document.getElementById('tr_header_points');
  const outerContainer = document.querySelector('.points-header-wrapper');
  const scrollContainer = document.querySelector('.table-points');

  if (!headerTable || !outerContainer || !scrollContainer) {
    return;
  }

  if (!outerContainer.classList.contains('fixed-header-table')) {
    outerContainer.dataset.stickyTop = outerContainer.offsetTop;
  }

  function syncHeaderScroll() {
    if (outerContainer.classList.contains('fixed-header-table')) {
      headerTable.style.marginLeft = `${-scrollContainer.scrollLeft}px`;
      outerContainer.style.width = `${scrollContainer.clientWidth}px`;
    }
  }

  function updateFixedState() {
    const stickyTop = Number(outerContainer.dataset.stickyTop || outerContainer.offsetTop);

    if (window.scrollY > stickyTop) {
      outerContainer.classList.add('fixed-header-table');
      syncHeaderScroll();
    } else {
      outerContainer.classList.remove('fixed-header-table');
      headerTable.style.marginLeft = '0px';
      outerContainer.style.width = '';
      outerContainer.dataset.stickyTop = outerContainer.offsetTop;
    }
  }

  if (!scrollContainer.dataset.pointsHeaderBound) {
    window.addEventListener('scroll', updateFixedState);
    scrollContainer.addEventListener('scroll', syncHeaderScroll);
    window.addEventListener('resize', updateFixedState);
    scrollContainer.dataset.pointsHeaderBound = '1';
  }

  updateFixedState();
}

function changeTable_NEW(userId, command) {
  changeTableMarker = true;
  if (command === 'showHis') {
    showHis_NEW(userId);
  } else if (command === 'hideManager') {
    hideManager_NEW(userId);
  }
  preparePerPage_NEW();
  updateTablePoints_NEW();
}

function handlerPointsDev(user) {
  userParam = user.getAllParam();
  allPoints = userParam.allPoints;
  filtredPoints = allPoints;

  const myCoWorkers = userParam.myCoWorkers.length > 0 ? userParam.myCoWorkers : null;
  allManagers = choiceUsersByRole('manager', myCoWorkers);
  managersTemporary = allManagers;
  
  preparePerPage_NEW();
  updateTablePoints_NEW();
  showHiddenPoints_NEW();
  searchPoints_NEW();
  controlPerPage(where, false);
}

function searchPoints_NEW() {
  const searchQuery = document.querySelector('#search_points');

  if (searchQuery) {
    searchQuery.addEventListener('input', function () {
      filtredPoints = allPoints.filter(point => point.namePoint.toLowerCase().includes(searchQuery.value.toLowerCase()));
      updateTablePoints_NEW();
    });
  }
}

function showHiddenPoints_NEW() {
  const showHidden = document.querySelector('#show-hidden-points');
  if (showHidden) {
    showHidden.addEventListener('click', function () {
      if (!showHidden.classList.contains('disabled-button')) {
        showHidden.classList.add('disabled-button');
        changeTableMarker = false;
        resetUserParam_NEW();
        updateTablePoints_NEW();
      }
    });
  }
}

function preparePerPage_NEW() {
  temporaryPoints = filtredPoints.slice((currentPage - 1) * perPage, currentPage * perPage);
  userParam.countPage = Math.ceil(filtredPoints.length / perPage);
}

function prepareUpdatePoints_NEW() {
  updateTablePoints_NEW();
}

function updateTablePoints_NEW() {
  preparePerPage_NEW();
  if (changeTableMarker) {
    const showHidden = document.querySelector('#show-hidden-points');
    showHidden.classList.remove('disabled-button');
  }
  let headerPoints = document.querySelector('.header_points');
  let containerPointsManager = document.querySelector('.main_container_points');
  headerPoints.innerHTML = '';
  containerPointsManager.innerHTML = '';
  packageHeadersManagersFlexible_NEW(headerPoints);
  packagePointsTable_NEW(containerPointsManager);
  const gridTemplate = createFlexibleGridTemplate_NEW('320px', '128px');
  applyGridTemplateToPoints_NEW(gridTemplate);
  fixedHeaderPointsTable();
  updatePagination(userParam.countPage, currentPage, where, false);
}

function packageHeadersManagersFlexible_NEW(headerPoints) {
  let sumManagers = 0;
  let collumnAllPoints = createTagHtml('div', 'collumn-points', 'Все точки');
  headerPoints.appendChild(collumnAllPoints);
  managersTemporary.forEach((user, index) => {
    if (user.points) {
      user.points = Array.isArray(user.points) ? user.points : [user.points];
    }
    const sumPoints = user.points ? user.points.length : 0;
    const contentLabel = '<b>' + sumPoints + '</b><br>' + user.firstname + '<br>' + user.lastname;

    if (user.statusActivity !== 'blocked') {
      sumManagers++;
      if (user.points) {
        user.points = Array.isArray(user.points) ? user.points : [user.points];
      }

      const contentBlock = createTagHtml('span', 'content-block', contentLabel);
      const arrow = createTagHtml('span', 'arrow-select-manager');
      const controlBlock = createTagHtml('div', 'control-block');
      const showOnlyHisPoints = createTagHtml('div', 'show-only-my-points', 'Его точки');
      const hideManager = createTagHtml('div', 'hide-manager', 'Скрыть');

      showOnlyHisPoints.addEventListener('click', function () {
        changeTable_NEW(user.idUser, 'showHis');
      });

      hideManager.addEventListener('click', function () {
        changeTable_NEW(user.idUser, 'hideManager');
      });

      massAppendChild(
        controlBlock,
        showOnlyHisPoints, hideManager
      );

      const collumnManager = createTagHtml('div', 'choice_points_manager', '', '', '', ['id-manager', user.idUser]);
      massAppendChild(
        collumnManager,
        contentBlock, arrow, controlBlock
      );

      headerPoints.appendChild(collumnManager);
    }
  });

}


function packagePointsTable_NEW(containerPointsManager) {
  if (changeTableMarker) {
    const showHidden = document.querySelector('#show-hidden-points');
    showHidden.classList.remove('disabled-button');
  }
  temporaryPoints.forEach((point, index) => {
    const rowPoint = createTagHtml('div', 'row-point');
    const contentLabel = point.namePoint;
    const collumnPoint = createTagHtml('div', 'point-name', contentLabel, '', '', ['id-point', point.idPoint]);
    rowPoint.appendChild(collumnPoint);
    containerPointsManager.appendChild(rowPoint);
    managersTemporary.forEach((user, index) => {
      if (user.statusActivity !== 'blocked') {
        const isChecked = checkPoint(point.idPoint, user.points) ? 'checked' : '';
        const divPointRadio = createTagHtml('div', 'collumn-point-radio');
        const inputManager = createTagHtml('input', 'radio-button-point', '', '', 'checkbox', ['id-input-points', point.idPoint]);
        inputManager.checked = isChecked;
        inputManager.type = 'radio';
        inputManager.name = 'point-' + point.idPoint;
        inputManager.value = user.idUser;
        divPointRadio.appendChild(inputManager);
        rowPoint.appendChild(divPointRadio);
        inputManager.addEventListener('change', function () {
          prepareDataPoints(user.idUser, point.idPoint);
          changeUserParam(user.idUser, point.idPoint);
        })
      }
    });
  });
}

function checkPoint(pointId, userPoints) {
  userPoints = Array.isArray(userPoints) ? userPoints : [userPoints];
  if (userPoints) {
    return userPoints.some(userPoint => {
      return parseInt(pointId) === parseInt(userPoint);
    });
  }
}

function createFlexibleGridTemplate_NEW(firstColumnWidth, repeatColumnWidth) {
  let totalColumns = 0;
  managersTemporary.forEach((user, index) => {
    if (user.statusActivity !== 'blocked') {
      totalColumns++;
    }
  });

  if (totalColumns === 0) {
    return firstColumnWidth;
  }
  const repeatTemplate = `repeat(${totalColumns}, ${repeatColumnWidth})`;
  return `${firstColumnWidth} ${repeatTemplate}`;
}

function applyGridTemplateToPoints_NEW(gridTemplate) {
  const headerPoints = document.querySelector('.header_points');
  const rows = document.querySelectorAll('.row-point');

  if (headerPoints) {
    headerPoints.style.gridTemplateColumns = gridTemplate;
  }

  rows.forEach((row) => {
    row.style.gridTemplateColumns = gridTemplate;
  });
}

function applyGridTemplate_NEW(element, firstColumnWidth, repeatColumnWidth) {
  const gridTemplate = createFlexibleGridTemplate_NEW(firstColumnWidth, repeatColumnWidth);
  element.style.gridTemplateColumns = gridTemplate;
  return element;
}

function prepareDataPoints(idManager, idPoint) {
  let jsonData = {
    idManager: idManager,
    idPoint: idPoint
  }
  let check = false;
  dataPoints.forEach(item => {
    if (item.idPoint === idPoint) {
      item.idManager = idManager;
      check = true;
    }
  });
  if (!check) {
    dataPoints.push(jsonData);
  }
}

function checkPoint(pointId, userPoints) {

  userPoints = Array.isArray(userPoints) ? userPoints : [userPoints];
  if (userPoints) {
    return userPoints.some(userPoint => {
      return parseInt(pointId) === parseInt(userPoint);
    });
  } else {
    return false;
  }
}

function choiceUsersByRole(role, users) {
  const group = users.find(group => group.roleUsers === role);
  if (group) {
    return group.users; // Возвращаем найденных пользователей
  }
  return null;
}

function showHis_NEW(userId) {
  const manager = allManagers.find(manager => manager.idUser === userId);
  const points = manager.points;
  temporaryPoints = [];
  filtredPoints = [];
  allPoints.forEach(point => {
    if (points.includes(point.idPoint)) {
      filtredPoints.push(point);
    }
  });
  temporaryPoints = filtredPoints;
}

function hideManager_NEW(userId) {
  hideManagers.push(userId);
  managersTemporary = [];
  allManagers.forEach(manager => {
    if (!hideManagers.includes(manager.idUser)) {
      managersTemporary.push(manager);
    }
  });
  temporaryPoints = [];
  filtredPoints = [];
  allPoints.forEach(point => {
    if (!hideManagers.includes(point.idManager)) {
      filtredPoints.push(point);
    }
  });
  temporaryPoints = filtredPoints;
}


function changeUserParam(idManager, idPoint) {
  userParam.myCoWorkers.forEach(items => {
    Object.values(items).forEach(workers => {
      if (workers && typeof workers === 'object') {
        Object.values(workers).forEach(worker => {
          if (worker && typeof worker === 'object') {
            if (worker.points && worker.points.includes(idPoint)) {
              worker.points.splice(worker.points.indexOf(idPoint), 1);
            }
          }
        });
      }
    });
  });
  userParam.myCoWorkers.forEach(items => {
    Object.values(items).forEach(workers => {
      if (workers && typeof workers === 'object') {
        Object.values(workers).forEach(worker => {
          if (worker && typeof worker === 'object') {
            if (worker.idUser === idManager) {
              worker.points.push(idPoint);
            }
          }
        });
      }
    });
  });
  userParam.allPoints.forEach(point => {
    if (point.idPoint === idPoint) {
      point.idManager = idManager;
    }
  });
  updateTablePoints_NEW();
}

function resetUserParam_NEW() {
  temporaryPoints = [];
  managersTemporary = [];
  hideManagers = [];
  filtredPoints = allPoints;
  temporaryPoints = filtredPoints;
  managersTemporary = allManagers;
}


