"use strict";
const where = 'points';
callListenerButton(where, 'save-', 'manager');

// function callPointsControl(user) {
//   let userParam = user.getAllParam();
//   controlPointsUsers(userParam);
//   socket.send('showPoints::: ');
//   socket.onmessage = function (event) {
//     let splitData = event.data.split('::: ');
//     const data = splitData[1];
//     switch (splitData[0]) {
//       case "paramData":
//         user.updatePoints(data);
//         // user.setAllPoints(data.allPoints);
//         userParam = user.getAllParam(splitData[1]);
//         controlPointsUsers(userParam);
//         break;
//     }
//   }
// }

function handlerConnectPoints(user) {
  const userParam = user.getAllParam();
  // const myPoint = Object.values(user.idPoint).length > 0 ? user.idPoint : null;
  const allPoints = Object.values(userParam.allPoints);
  const myCoWorkers = userParam.myCoWorkers.length > 0 ? userParam.myCoWorkers : null;
  const managers = choiceUsersByRole('manager', myCoWorkers);
  let containerManagers = document.querySelector('.choice_manager_div');
  let containerPointsManager = document.querySelector('.main_container_points');

  containerManagers.innerHTML = '';
  containerPointsManager.innerHTML = '';

  containerManagers = packageContainerManagers(managers, containerManagers);
  containerPointsManager = packageContainerPoints(managers, allPoints, containerPointsManager);
}

function packageContainerManagers(users, containerManagers) {
  users.forEach((user, index) => {
    //Define all element
    if (user.statusActivity !== 'blocked') {
      if (user.points) {
        user.points = Array.isArray(user.points) ? user.points : [user.points];
      }
      const sumPoints = user.points ? user.points.length : 0;
      const idHTML = 'manager-' + user.idUser;
      const inputManger = createTagHtml('input', 'choice_points_manager', '', idHTML, 'checkbox', ['id-manager', user.idUser]);
      const contentLabel = user.firstname + " " + user.lastname + '(' + sumPoints + ')';
      const labelManager = createTagHtml('label', '', contentLabel, idHTML);

      inputManger.checked = index === 0 ? true : false;
      massAppendChild(
        containerManagers,
        inputManger, labelManager
      );
      inputManger.addEventListener('change', function () {
        checkStatusInputManager(inputManger);
      })
    }
  });
  return containerManagers;
}

function packageContainerPoints(users, allPoints, containerPointsManager) {
  users.forEach((user, index) => {
    let pointsMainBoxUser = createTagHtml('div', 'main_box_points', '', '', '', ['id-block-points', user.idUser]);
    let titleContent = 'Точки менеджера: ' + user.firstname + ' ' + user.lastname;
    let title = createTagHtml('h3', '', titleContent);
    let pointsDiv = createTagHtml('div', 'points_div');

    pointsMainBoxUser.style.display = index === 0 ? "block" : "none";

    containerPointsManager.appendChild(pointsMainBoxUser);
    massAppendChild(
      pointsMainBoxUser,
      title, pointsDiv
    )

    const userPoints = user.points;
    allPoints.forEach(point => {
      const isChecked = checkPoint(point.idPoint, userPoints);
      const idHTML = 'point-' + point.idPoint;
      let pointBox = createTagHtml('div', 'points_box');
      let inputPoint = createTagHtml('input', 'checkbox_points', '', idHTML, 'checkbox', ['id-input-points', point.idPoint]);
      let labelPoint = createTagHtml('label', '', point.namePoint, idHTML);
      inputPoint.value = point.idPoint;
      inputPoint.checked = isChecked;
      pointsDiv.appendChild(pointBox);
      massAppendChild(
        pointBox,
        inputPoint, labelPoint
      )
    })
  });
  return containerPointsManager;
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

// listenerButtonDropList('points', '-save');




