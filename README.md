<div align="center">
  <a href="https://github.com/KarloZ7715/fingerprintweb">
    <img src=".github/assets/banner.svg" alt="ElectroCode Animated Banner" width="100%">
  </a>

  <br>

  <div style="display: flex; justify-content: center; gap: 15px; margin-top: 20px;">
    <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" height="28">
    <img src="https://img.shields.io/badge/Filament-F28D1A?style=for-the-badge&logo=filament&logoColor=white" height="28">
    <img src="https://img.shields.io/badge/Livewire-4E56A6?style=for-the-badge&logo=livewire&logoColor=white" height="28">
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" height="28">
    <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" height="28">
  </div>

  <br>
</div>

---

<div align="center">
  <blockquote>
    <h3>Innovación y Seguridad en Sincronía</h3>
    <p>
      <b>ElectroCode</b> redefine la gestión corporativa fusionando la seguridad física con la eficiencia administrativa. 
      Más que un simple sistema de asistencia, es una plataforma viva que conecta el flujo de personal con tecnología IoT en tiempo real.
    </p>
  </blockquote>
</div>

<br>

## La Propuesta de Valor

<table border="0">
  <tr>
    <td width="50%" valign="top" style="border: none;">
      <h3 align="center">Seguridad Activa</h3>
      <p>Monitoreo constante y reacción inmediata. El sistema integra un módulo de alarmas con gestión de estados en tiempo real.</p>
      <ul>
        <li>Estados: <code>Activa</code>, <code>En Espera</code>, <code>Apagada</code></li>
        <li>Control remoto vía Web</li>
        <li>Feedback instantáneo de sensores</li>
      </ul>
    </td>
    <td width="50%" valign="top" style="border: none;">
      <h3 align="center">Biometría Avanzada</h3>
      <p>La identidad es la llave. Nuestro módulo de <b>Enrollment</b> gestiona huellas dactilares con precisión milimétrica.</p>
      <ul>
        <li>Sincronización Server ↔ IoT</li>
        <li>Wizard de registro en 3 pasos</li>
        <li>Validación de calidad de huella</li>
      </ul>
    </td>
  </tr>
</table>

<br>

## Stack Tecnológico

<details>
  <summary><b>Click para desplegar detalles técnicos</b></summary>
  <br>
  <div align="center">
    <table>
      <thead>
        <tr>
          <th>Layer</th>
          <th>Tecnología</th>
          <th>Descripción</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><b>Backend Core</b></td>
          <td><code>Laravel 12</code></td>
          <td>Framework PHP robusto y seguro (PHP 8.2+)</td>
        </tr>
        <tr>
          <td><b>Admin Interface</b></td>
          <td><code>FilamentPHP v4</code></td>
          <td>Panel administrativo TALL stack de última generación</td>
        </tr>
        <tr>
          <td><b>Interactivity</b></td>
          <td><code>Livewire & Alpine.js</code></td>
          <td>Experiencia de usuario dinámica sin complejidad de SPA</td>
        </tr>
        <tr>
          <td><b>IoT Firmware</b></td>
          <td><code>C++ / Arduino</code></td>
          <td>Lógica de control para microcontroladores ESP32</td>
        </tr>
        <tr>
          <td><b>Infraestructura</b></td>
          <td><code>Docker</code></td>
          <td>Contenedorización completa para despliegue consistente</td>
        </tr>
      </tbody>
    </table>
  </div>
</details>

<br>

## Arquitectura IoT

El corazón invisible del sistema. Una arquitectura de **Polling** y colas de comandos asíncronas orquesta la comunicación con microcontroladores ESP32.

<div align="center">

```mermaid
%%{init: {'theme': 'base', 'themeVariables': { 'primaryColor': '#ffcc00', 'edgeLabelBackground':'#ffffff', 'tertiaryColor': '#fff'}}}%%
graph LR
    A[Admin Panel] -->|Comando| B(Base de Datos)
    B -->|Polling| C{API Service}
    C -->|JSON| D[ESP32 Device]
    D -->|Respuesta| C
    C -->|Update| B
    B -->|Livewire| A

    style A fill:#2d3748,stroke:#cbd5e0,stroke-width:2px,color:#fff
    style B fill:#2d3748,stroke:#cbd5e0,stroke-width:2px,color:#fff
    style C fill:#4a5568,stroke:#cbd5e0,stroke-width:2px,color:#fff
    style D fill:#2d3748,stroke:#cbd5e0,stroke-width:2px,color:#fff

    linkStyle default stroke:#cbd5e0,stroke-width:2px;
```

</div>

---

<div align="center">
  <br>
  <p><b>ElectroCode Team</b></p>
  <p><i>Innovación y seguridad en cada línea de código.</i></p>
  <p>© 2025</p>
  <br>
</div>
