<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
use Core\Database;
?>
<!-- Aqui começa o HTML do menu -->
<!-- Botão Hamburger Menu para mobile -->
<button class="btn btn-primary d-md-none m-3 position-fixed top-0 start-0 z-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuIngresso" aria-controls="offcanvasMenuIngresso" style="width:48px;height:48px;">
  <span class="fs-2">&#9776;</span>
</button>
<!-- Offcanvas Menu -->
<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="offcanvasMenuIngresso" aria-labelledby="offcanvasMenuIngressoLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasMenuIngressoLabel">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
  </div>
  <div class="offcanvas-body p-0">
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-white vh-100">
      <!-- Conteúdo do menu -->
      <a href="index.php" class="d-flex align-items-center mb-4 text-decoration-none">
        <span class="fs-3 fw-bold text-primary">🍔 Festa do Hambúrguer</span>
      </a>
      <ul class="nav nav-pills flex-column mb-auto gap-2">
        <li class="nav-item">
          <a href="index.php?c=ingresso" class="nav-link d-flex align-items-center">
            <span class="fs-5 me-2">🎟️</span> Ingressos
          </a>
        </li>
        <li>
          <a href="index.php?c=ingresso&a=vincular" class="nav-link d-flex align-items-center">
            <span class="fs-5 me-2">🔗</span> Vincular Ingresso
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>
<!-- Menu fixo para desktop -->
<div class="d-none d-md-flex flex-column flex-shrink-0 p-3 bg-white shadow vh-100" style="width: 240px;">
  <a href="index.php" class="d-flex align-items-center mb-4 text-decoration-none">
    <span class="fs-3 fw-bold text-primary">🍔 Festa do Hambúrguer</span>
  </a>
  <ul class="nav nav-pills flex-column mb-auto gap-2">
    <li class="nav-item">
      <a href="index.php?c=ingresso" class="nav-link d-flex align-items-center">
        <span class="fs-5 me-2">🎟️</span> Ingressos
      </a>
    </li>
    <li>
      <a href="index.php?c=ingresso&a=vincular" class="nav-link d-flex align-items-center">
        <span class="fs-5 me-2">🔗</span> Vincular Ingresso
      </a>
    </li>
    <li>
      <a href="index.php?c=fila&a=fila" class="nav-link d-flex align-items-center">
        <span class="fs-5 me-2">⏳</span> Fila
      </a>
    </li>
    <!--
    <li>
      <a href="index.php?c=fila&a=entrada" class="nav-link d-flex align-items-center">
        <span class="fs-5 me-2">🍔</span> Entrega
      </a>
    </li>
    -->
    <li>
      <a href="index.php?c=dashboard" class="nav-link d-flex align-items-center">
        <span class="fs-5 me-2">📊</span> Dashboard
      </a>
    </li>
  </ul>
</div> 