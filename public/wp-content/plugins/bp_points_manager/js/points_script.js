let allPointsDOC = document.querySelectorAll(".point");
function totalSelectedPoints() {
  let totalSelectedPoints = 0;
  allPointsDOC.forEach(function (point) {
    if (point.checked) {
      totalSelectedPoints++;
    }
  });
  let spanTotalPoints = document.getElementById("total_points_checked");
  spanTotalPoints.innerHTML = totalSelectedPoints;
}

document.addEventListener("DOMContentLoaded", function () {
  let buttonManagers = document.querySelectorAll(".choice_points_manager");

  buttonManagers.forEach(function (buttonManager) {
    buttonManager.addEventListener("click", function () {
      let buttonManagerId = buttonManager.id;
      let idManager = parseInt(buttonManagerId.replace("manager-", ""), 10);
      let boxPoints = document.getElementById(
        "manager_points_box_" + idManager
      );

      let managerPoints = boxPoints.querySelectorAll(".point");
      let managerBoxes = document.querySelectorAll(".points_main_box");

      let allPointsButton = document.getElementById("manager-0");
      let boxAllPoints = document.getElementById("manager_points_box_0");
      let allPoints = boxAllPoints.querySelectorAll(".point");

      if (idManager) {
        if (buttonManager.checked) {
          //Работа над всеми точками
          boxAllPoints.style.display = "none";
          allPointsButton.checked = false;
          allPoints.forEach(function (point) {
            point.checked = false;
          });
          //Работа над точками Выброного менеджера
          handlePointsChecked(boxPoints, managerPoints);
        } else {
          handlePointsUnchecked(buttonManager, boxPoints);
        }
      } else {
        if (buttonManager.checked) {
          //Выключаем точки всех остальных менеджеров
          buttonManagers.forEach(function (otherButton) {
            otherButton.checked = false;
          });
          buttonManager.checked = true;
          managerBoxes.forEach(function (boxes) {
            boxes.style.display = "none";
            managerPoints = boxes.querySelectorAll(".point");
            managerPoints.forEach(function (point) {
              point.checked = false;
            });
          });
          //Работа над всеми точками
          handlePointsChecked(boxAllPoints, allPoints);
        } else {
          handlePointsUnchecked(buttonManager, boxPoints);
        }
      }
      totalSelectedPoints();
    });
  });
  allPointsDOC.forEach(function (point) {
    point.addEventListener("click", totalSelectedPoints);
  });
  function handlePointsChecked(box, points) {
    box.style.display = "block";
    points.forEach(function (point) {
      point.checked = true;
    });
  }
  function handlePointsUnchecked(button, box) {
    box.style.display = "none";
    let allPoints = box.querySelectorAll(".point");
    allPoints.forEach(function (point) {
      point.checked = false;
    });
  }
  // Функция для подсчета общего количества выбранных точек
  document
    .querySelector(".point_save")
    .addEventListener("click", function (event) {
      event.preventDefault(); // Предотвращаем стандартное поведение кнопки

      // Получаем выбранные точки
      let selectedPoints = [];
      allPointsDOC.forEach(function (point) {
        if (point.checked) {
          selectedPoints.push(point.id.replace("point-", ""));
          console.log(selectedPoints);
        }
      });

      // Отправляем данные на сервер
      fetch("/wp-content/plugins/bp_points_manager/dannie.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body:
          "action=save_points&selectedPoints=" + JSON.stringify(selectedPoints),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Network response was not ok");
          }
          const contentType = response.headers.get("content-type");
          if (contentType && contentType.includes("application/json")) {
            return response.json();
          } else {
            throw new Error("Invalid content type received from server");
          }
        })
        .then((data) => {
          console.log("Сервер ответил:", data);
          // Дополнительная логика обработки ответа, если необходимо
          location.reload();
        })
        .catch((error) =>
          console.error("Ошибка при отправке данных на сервер:", error.message)
        );
    });

  document
    .querySelector(".point_default")
    .addEventListener("click", function (event) {
      event.preventDefault(); // Предотвращаем стандартное поведение кнопки

      // Отправляем данные на сервер
      fetch("/wp-content/plugins/bp_points_manager/reset.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=reset_points",
      })
        .then((response) => response.json())
        .then((data) => {
          console.log("Сервер ответил:", data);
          // Дополнительная логика обработки ответа, если необходимо
          location.reload();
        })
        .catch((error) =>
          console.error("Ошибка при отправке данных на сервер:", error)
        );
    });
});
