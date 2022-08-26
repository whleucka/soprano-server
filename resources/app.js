import React from "react";
import App from "./components/App";
import * as ReactDOMClient from "react-dom/client";

/* globals __webpack_public_path__ */
__webpack_public_path__ = "/assets/bundle/";

const container = document.getElementById("app");

// Create a root.
const root = ReactDOMClient.createRoot(container);
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
