"use strict";class mainUser{#idUser;#firstname;#lastname;#role;#position;#statusActivity;#whose;#potencialReplacement;#myWorkers;#myCoWorkers;#potencialParticipiant;#idPoint;#allPoints;#emailNotification;#connected;constructor(data){this.#idUser=data.idUser;this.#firstname=data.firstname;this.#lastname=data.lastname;this.#role=data.role;this.#position=definePositionUser(data.role);this.#statusActivity=data.statusActivity;this.#emailNotification=data.emailNotification;this.#whose=data.whose;this.#connected=data.connected;if(data.allPoints){this.#allPoints=data.allPoints;}
this.#idPoint=data.idPoint?data.idPoint:[];this.#potencialReplacement=data.potencialReplacement;if(data.myWorkers){this.#myWorkers=this.#sortWorkers(data.myWorkers);this.#myWorkers=Object.values(this.#myWorkers);}
if(data.myCoWorkers){this.#myCoWorkers=this.#sortWorkers(data.myCoWorkers);this.#myWorkers=Object.values(this.#myCoWorkers);}
if(data.potencialReplacement){this.#potencialReplacement=data.potencialReplacement;}
let notifierConnected;if(!this.#connected){console.log('Ку-ку');notifierConnected=createNotifier(this.#firstname+' '+this.#lastname+', вы не подключены к системе личного кабинета!','Вероятнее всего, ваш региональный менеджер забыл вас подключить к системе, свяжитесь с ним и скажите ему об этом.');document.body.appendChild(notifierConnected);}}
getAllParam(){let param={'idUser':this.#idUser,'firstname':this.#firstname,'lastname':this.#lastname,'position':this.#position,'statusActivity':this.#statusActivity,'potencialReplacement':this.#potencialReplacement,'myWorkers':this.#myWorkers,'myCoWorkers':this.#myCoWorkers,'whose':this.#whose,'potencialParticipiant':this.#potencialParticipiant,'emailNotification':this.#emailNotification,}
if(this.#idPoint){param.idPoint=this.#idPoint}
if(this.#allPoints){param.allPoints=this.#allPoints}
if(this.#myWorkers){param.myWorkers=this.#myWorkers;}else if(this.#myCoWorkers){param.myCoWorkers=this.#myCoWorkers;}
return param;}#sortWorkers(workers){let groupedWorkers=Object.values(workers).reduce((acc,worker)=>{if(!acc[worker.role]){acc[worker.role]=[];}
acc[worker.role].push(worker);return acc;},{});let result=Object.keys(groupedWorkers).map(role=>{return{roleUsers:role,users:groupedWorkers[role]};});return result;}
getIdUser(){return this.#idUser;}
getName(){return this.#firstname;}
getLastname(){return this.#lastname;}
getPosition(){return this.#position;}
getStatus(){return this.#statusActivity;}
getReplacements(){return this.#potencialReplacement;}
getMyWorkers(){return this.#myWorkers;}
getMyCoWorkers(){return this.#myCoWorkers;}
getPotencialParticipiant(){return this.#potencialParticipiant;}
setPotencialParticipiant(data){this.#potencialParticipiant=data.sort((a,b)=>{if(a.whose==="contractor"&&b.whose!=="contractor")return-1;if(a.whose!=="contractor"&&b.whose==="contractor")return 1;return a.displayName.localeCompare(b.displayName,'ru');});}
setIdPoint(data){if(data){this.#idPoint=data;}}
setAllPoints(data){this.#allPoints=data;}
setMyWorkers(data){if(data){this.#myWorkers=this.#sortWorkers(data);this.#myWorkers=Object.values(this.#myWorkers);}}
setMyCoWorkers(data){if(data){this.#myCoWorkers=this.#sortWorkers(data);this.#myWorkers=Object.values(this.#myCoWorkers);}}
updatePoints(data){const dataNew=JSON.parse(data);console.log(dataNew);this.setIdPoint(dataNew.idPoint);this.setMyWorkers(dataNew.myWorkers);this.setMyCoWorkers(dataNew.myCoWorkers);}}
let data;