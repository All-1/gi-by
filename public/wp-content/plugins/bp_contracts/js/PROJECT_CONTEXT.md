# Component Context: bp_contracts/Frontend (JS)

## Overview
The frontend is a **WebSocket-driven Single Page Application (SPA)** embedded within the WordPress admin dashboard. It relies on a persistent connection to the PHP `Chat` server to receive state updates in real-time.

## 1. Core: `communication_server.js`
**Role**: The Network Client.
- **Connection**: Manages `new WebSocket('ws://...')`.
- **Heartbeat**: Implements ping/pong and auto-reconnection logic.
- **Router**: The `onmessage` handler acts as a dispatcher:
    ```javascript
    switch (title) {
        case 'idUser': saveSession(body); break;
        case 'showContracts': contracts.renderGrid(body); break;
        case 'Notification': notification.show(body); break;
    }
    ```

## 2. Contracts Grid: `contracts.js`
**Role**: The Main Dashboard UI.
- **Grid Rendering**: Dynamically builds the HTML table of contracts.
- **Search/Filter**: Captures input → sends `searchQueryContracts` WS message → updates grid on response.
- **Pagination**: Client-side handling of page state, server-side data fetching.

## 3. Modal Manager: `modal_window.js`
**Role**: Handles all "Popups".
- **Dynamic Content**: Injection of HTML forms (e.g., "Create New Contract", "Add Participant").
- **Context Awareness**: Knows which `contractID` triggered the modal.

## 4. Document Generation (`exceljs`, `pdfmake`)
**Role**: Client-Side export.
- **Architecture**: Instead of generating PDFs on the server, the raw JSON data is sent to the client, where JS libraries render the PDF. This offloads CPU from the PHP server.

## Data Flow
1.  **User Action**: Click "Create Contract".
2.  **JS**: Sends `newContract` JSON to WS.
3.  **PHP**: Processes, updates DB, sends `showContracts` broadcast.
4.  **JS**: Receives `showContracts` -> Re-renders the Grid.
