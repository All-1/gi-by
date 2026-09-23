"use strict";
const userId = parseInt(Id);
const userRole = role;
const whose = partyVerification(userRole);
const server = 'ws://127.0.0.1:8080';
// const server = 'wss://dev.gi.by/wss/';
// const server = 'wss://gi.by/wss/';
// const server = 'wss://geosideal.ru/wss/';

const serverUrl = 'https://geosideal.ru';
// const serverUrl = 'https://dev.gi.by';
// let potencialParticipiant;
let flagSecondMessageDialog = false; // Костыль
let socket; // A little bit later this variable will isolate and incorporate it in the function openConnection which will include as a method on an object Socket.
let lastSN = [];
let notifier;
let heartbeatInterval;
let intervalReconnect = 1000;
let notifierDisconnect;
sessionStorage.setItem('isPageReload', 'false');

window.addEventListener('beforeunload', function (event) {
  // Mark that the page is being reloaded or closed.
  sessionStorage.setItem('isPageReload', 'true');
});
document.addEventListener('visibilitychange', handleVisibilityChange);

openConnection(server);
reconection();

function handleVisibilityChange() {
  if (document.visibilityState === 'hidden') {
    intervalReconnect = 60000;
  } else if (document.visibilityState === 'visible') {
    intervalReconnect = 1000;
    if (!socket || socket.readyState !== WebSocket.OPEN) {
      clearInterval(heartbeatInterval);
      startHeartbeat(server);
    }
  } else if (document.visibilityState === 'prerender') {
    intervalReconnect = 1000;
    if (!socket || socket.readyState !== WebSocket.OPEN) {
      clearInterval(heartbeatInterval);
      startHeartbeat(server);
    }
  }
}

function reconection() {
  setTimeout(() => {
    if (!socket || socket.readyState !== WebSocket.OPEN) {
      clearInterval(heartbeatInterval);
      startHeartbeat(server);
    }
  }, 1000);
}

function openConnection(server) {
  if (socket) {
    socket.close(); // Безопасно закрываем предыдущее соединение
    socket = null;
  }
  socket = new WebSocket(server);

  socket.onopen = function (event) {
    if (socket.readyState === WebSocket.OPEN) {
      // Пример отправки сообщения на сервер
      try {
        socket.send('idUser::: ' + userId);
        if (typeof setDefaultState !== 'undefined') {
          setDefaultState(where);
        }
        inicializationWebsocket(socket);
        if (typeof where !== 'undefined') {
          reloadDataForWindow(where, lastSN);
        }
      } catch (error) {
        socket = null;
      }
    }
  }
  // Запускаем проверку соединения (heartbeat)
  socket.onerror = function (error) {
    // console.error('WebSocket error:', error);
    closeConnection(socket);
  };
  socket.onclose = function () {
    const isReload = sessionStorage.getItem('isPageReload');
    if (isReload === 'false') {
      let now = new Date();
      // console.warn('Соединение закрыто. Попытка переподключения через 1 секунду.' + now);
      startHeartbeat(server);
    }
  };
}

function startHeartbeat(server) {
  if (!notifierDisconnect) {
    notifierDisconnect = notifierDisconnect ? notifierDisconnect : createNotifier(
      'Произошёл разрыв соединения с сервером!',
      'Подождите пару секунд, мы пытаемся переподключить Вас!'
    );
    document.body.appendChild(notifierDisconnect);
  }
  let notifierConnect = createNotifier(
    'Мы восстановили соединение с сервером!',
    'Вы можете продолжить с того мееста где остановились!'
  );
  heartbeatInterval = setInterval(() => {
    if (!socket || socket.readyState !== WebSocket.OPEN) {
      openConnection(server);
    } else {
      if (notifierDisconnect) {
        notifierDisconnect.remove();
        notifierDisconnect = null;
      }
      document.body.appendChild(notifierConnect);
      setInterval(() => {
        notifierConnect.remove();
      }, 2000);
      clearInterval(heartbeatInterval); // Убираем таймер, если соединение восстановлено
    }
  }, intervalReconnect);
}

function closeConnection(socket) {
  if (heartbeatInterval) {
    clearInterval(heartbeatInterval); // Очищаем старый таймер, если он есть
  }
  if (socket) {
    socket.close();
    socket = null;
  }
}

function reloadDataForWindow(where, lastSN) {
  let newWhere = choseWhereForWindow(where);
  let nameProperty = newWhere + 'SN';
  if (lastSN[nameProperty] !== undefined) {
    invokeDataForWindow(where, lastSN[nameProperty]);
  }
}

function createNotifier(title, body, button = null) {
  let shadeBoxNotification = createTagHtml('div', 'shade-full-screen-box');
  let blockDisconnectNotification = createTagHtml('div', 'box-denied-notification');
  let titleDisconnectNotification = createTagHtml('h4', 'title-denied', title);
  let textDisconnectNotification = createTagHtml('p', 'text-denied-notification', body);
  let buttonClose = button ? createTagHtml('button', 'close-denied-notification', button) : '';
  massAppendChild(
    blockDisconnectNotification,
    titleDisconnectNotification, textDisconnectNotification
  );
  if (buttonClose) {
    blockDisconnectNotification.appendChild(buttonClose);
    buttonClose.addEventListener('click', () => {
      shadeBoxNotification.remove();
    });
  }
  shadeBoxNotification.appendChild(blockDisconnectNotification);

  return shadeBoxNotification;
}


function handlerRequestFromServer(caseTitle, data, user) {
  const functionName = 'handler' + toCapsCase(caseTitle); // Формируем имя функции
  if (typeof window[functionName] === 'function') {
    const jsonData = JSON.parse(data);
    window[functionName](jsonData, user);
  } else {
    // console.warn(`Function "${functionName}" is not defined`);
  }
}

function inicializationWebsocket(socket) {
  let user;
  const setUser = (newUser) => {
    user = null;
    user = newUser; // Обновляем user через замыкание
    // let userParam = user.getAllParam();
  };
  socket.onmessage = function (event) {
    const splitData = event.data.split('::: ');
    const caseTitle = splitData[0];
    console.log("caseTitle: ", caseTitle);
    // console.log("splitData: ", splitData[1]);
    // console.log("user: ", user);
    if (caseTitle === 'paramForClient') {
      // console.log("paramForClient: ", splitData[1]);
      // console.log("user: ", user);
      
      handlerParamForClient(splitData[1], setUser);
      handlerParamForClientPages(user);
    } else if (caseTitle === 'Notification') {
      handlerNotification(splitData[1], user);
    } else {
      handlerRequestFromServer(caseTitle, splitData[1], user);
    }
  }
}

function handlerPotencialParticipiant(data, user) {
  if (data) {
    let potencialParticipiant = Object.values(data);
    potencialParticipiant = changeUserRole(potencialParticipiant);
    user.setPotencialParticipiant(potencialParticipiant);
  }
}

function handlerParamForClient(data, setUser) {
  const jsonData = JSON.parse(data);
  setUser(new mainUser(jsonData)); // Возвращаем нового пользователя
}

function handlerNotification(data, user) {
  const jsonData = JSON.parse(data);
  let userParam = user.getAllParam();
  if (!notifier) {
    notifier = new NotificationWorker(jsonData, userParam);
  } else {
    notifier.reloadNotifier(jsonData, userParam);
  }
}

function handlerParamForClientPages(user) {
  const path = window.location.pathname.replace(/^\/|\/$/g, '');

  const functionName = 'handler' + switchCommand(toCapsCase(path));
  if (typeof window[functionName] === 'function') {
    window[functionName](user);
  } else {
    console.warn(`Function "${functionName}" is not defined`);
  }
}








