import React from "react";
import ReactDOM from "react-dom/client";
import "./styles.css";

function App() {
  return (
    <main className="p-6 max-w-3xl mx-auto">
      <h1 className="text-3xl font-bold">FingerPrintWeb</h1>
      <p className="mt-4 text-gray-300">
        Frontend inicial listo. React 19 RC + Tailwind.
      </p>
    </main>
  );
}

ReactDOM.createRoot(document.getElementById("root") as HTMLElement).render(
  <App />
);
