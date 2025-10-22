<?php
require_once __DIR__ . '/../config/database.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Configuração de filtros para a lista de eventos
$status = isset($_GET['status']) ? $_GET['status'] : 'todos';
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'todos';

// Query para lista de eventos
$queryLista = "SELECT *, COALESCE(status, 'pendente') as status FROM eventos WHERE 1=1";
$paramsLista = [];

if ($status !== 'todos') {
    $queryLista .= " AND status = ?";
    $paramsLista[] = $status;
}

switch ($periodo) {
    case 'passados':
        $queryLista .= " AND data_evento < CURDATE()";
        break;
    case 'hoje':
        $queryLista .= " AND data_evento = CURDATE()";
        break;
    case 'futuros':
        $queryLista .= " AND data_evento > CURDATE()";
        break;
}

$queryLista .= " ORDER BY data_evento DESC, hora DESC";
$stmtLista = $pdo->prepare($queryLista);
$stmtLista->execute($paramsLista);
$listaEventos = $stmtLista->fetchAll();

// Obtém o mês e ano atuais ou os selecionados
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Calcula mês anterior e próximo mês
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Obtém o primeiro dia do mês
$firstDay = new DateTime("$year-$month-01");
$lastDay = new DateTime($firstDay->format('Y-m-t'));

// Busca eventos do mês atual
$stmt = $pdo->prepare("SELECT *, COALESCE(status, 'pendente') as status FROM eventos WHERE DATE_FORMAT(data_evento, '%Y-%m') = ?");
$stmt->execute([$firstDay->format('Y-m')]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Array para armazenar os dias com eventos
$diasComEventos = [];
foreach ($eventos as $evento) {
    $dia = date('j', strtotime($evento['data_evento']));
    if (!isset($diasComEventos[$dia])) {
        $diasComEventos[$dia] = [];
    }
    $diasComEventos[$dia][] = $evento;
}

// Adiciona log para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Nomes dos meses em português
$meses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// Processa o formulário de adição de evento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar_evento'])) {
    $data_evento = $_POST['data_evento'];
    $descricao = $_POST['descricao'];
    $hora = $_POST['hora'];
    $status = 'pendente'; // Define um status padrão
    
    $stmt = $pdo->prepare("INSERT INTO eventos (data_evento, descricao, hora, status) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$data_evento, $descricao, $hora, $status])) {
        $_SESSION['mensagem'] = "Evento adicionado com sucesso!";
        header("Location: index.php?page=calendario&month=$month&year=$year");
        exit();
    }
}

// Processa a edição do evento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_evento'])) {
    $evento_id = $_POST['evento_id'];
    $data_evento = $_POST['data_evento'];
    $hora = $_POST['hora'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE eventos SET data_evento = ?, hora = ?, descricao = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$data_evento, $hora, $descricao, $status, $evento_id])) {
        $_SESSION['mensagem'] = "Evento atualizado com sucesso!";
        header("Location: index.php?page=calendario&month=" . date('m', strtotime($data_evento)) . "&year=" . date('Y', strtotime($data_evento)));
        exit();
    }
}

// Processa a exclusão de evento
if (isset($_GET['excluir_evento'])) {
    $id_evento = $_GET['excluir_evento'];
    $stmt = $pdo->prepare("DELETE FROM eventos WHERE id = ?");
    if ($stmt->execute([$id_evento])) {
        $_SESSION['mensagem'] = "Evento excluído com sucesso!";
        header("Location: index.php?page=calendario&month=$month&year=$year");
        exit();
    }
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Calendário de Eventos</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventoModal">
                <i class="bi bi-plus-circle"></i> Novo Evento
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-success alert-dismissible fade show" id="successMessage" role="alert">
            <?php 
            echo $_SESSION['mensagem'];
            unset($_SESSION['mensagem']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" id="calendar-tab" data-bs-toggle="tab" href="#calendar" role="tab">Calendário</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="events-tab" data-bs-toggle="tab" href="#events" role="tab">Lista de Eventos</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Calendário -->
        <div class="tab-pane fade show active" id="calendar" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <!-- Seletor de Ano -->
                            <select class="form-select me-2" style="width: auto;" onchange="window.location.href='?page=calendario&month=<?php echo $month; ?>&year=' + this.value">
                                <?php
                                $currentYear = date('Y');
                                $startYear = $currentYear - 5;
                                $endYear = $currentYear + 5;
                                
                                for($y = $startYear; $y <= $endYear; $y++) {
                                    $selected = ($y == $year) ? 'selected' : '';
                                    echo "<option value='$y' $selected>$y</option>";
                                }
                                ?>
                            </select>

                            <!-- Seletor de Mês -->
                            <select class="form-select me-3" style="width: auto;" onchange="window.location.href='?page=calendario&month=' + this.value + '&year=<?php echo $year; ?>'">
                                <?php
                                foreach($meses as $num => $nome) {
                                    $selected = ($num == $month) ? 'selected' : '';
                                    echo "<option value='$num' $selected>$nome</option>";
                                }
                                ?>
                            </select>

                            <!-- Botões de navegação -->
                            <div class="btn-group">
                                <a href="?page=calendario&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                                <a href="?page=calendario&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <a href="?page=calendario" class="btn btn-outline-secondary">Hoje</a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Domingo</th>
                                <th>Segunda</th>
                                <th>Terça</th>
                                <th>Quarta</th>
                                <th>Quinta</th>
                                <th>Sexta</th>
                                <th>Sábado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $firstDayOfWeek = intval($firstDay->format('w'));
                            $lastDayOfMonth = intval($lastDay->format('d'));
                            
                            $currentDay = 1;
                            $calendar = "";
                            
                            for ($i = 0; $i < 6; $i++) {
                                $calendar .= "<tr>";
                                
                                for ($j = 0; $j < 7; $j++) {
                                    $calendar .= "<td class='calendar-day p-2' style='height: 100px; vertical-align: top;'>";
                                    
                                    if (($i == 0 && $j >= $firstDayOfWeek) || ($i > 0 && $currentDay <= $lastDayOfMonth)) {
                                        $isToday = ($currentDay == date('j') && $month == date('m') && $year == date('Y'));
                                        
                                        $calendar .= "<div class='d-flex justify-content-between align-items-start'>";
                                        $calendar .= "<span class='day-number" . ($isToday ? ' today' : '') . "'>$currentDay</span>";
                                        
                                        if (isset($diasComEventos[$currentDay])) {
                                            $modalId = "eventosModal" . $currentDay . "_" . $month . "_" . $year;
                                            $calendar .= "<span class='badge bg-primary' style='cursor: pointer;' 
                                                              data-bs-toggle='modal' 
                                                              data-bs-target='#" . $modalId . "'>
                                                            " . count($diasComEventos[$currentDay]) . " eventos
                                                        </span>";
                                            
                                            $calendar .= "
                                            <div class='modal fade' id='" . $modalId . "' tabindex='-1'>
                                                <div class='modal-dialog'>
                                                    <div class='modal-content'>
                                                        <div class='modal-header'>
                                                            <h5 class='modal-title'>Eventos do dia $currentDay</h5>
                                                            <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                        </div>
                                                        <div class='modal-body'>";
                                            foreach ($diasComEventos[$currentDay] as $evento) {
                                                $calendar .= "
                                                    <div class='card mb-2'>
                                                        <div class='card-body'>
                                                            <div class='d-flex justify-content-between align-items-start'>
                                                                <div>
                                                                    <h6 class='card-title'>" . htmlspecialchars($evento['descricao']) . "</h6>
                                                                    <p class='card-text'>
                                                                        <small class='text-muted'>Horário: " . 
                                                                        date('H:i', strtotime($evento['hora'])) . "</small>
                                                                        <br>
                                                                        <span class='badge " . ($evento['status'] == 'realizado' ? 'bg-success' : 'bg-warning') . "'>
                                                                            " . ucfirst($evento['status']) . "
                                                                        </span>
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <button type='button' class='btn btn-primary btn-sm me-1'
                                                                            data-bs-toggle='modal'
                                                                            data-bs-target='#editEventoModal'
                                                                            data-evento-id='" . $evento['id'] . "'
                                                                            data-evento-data='" . $evento['data_evento'] . "'
                                                                            data-evento-hora='" . $evento['hora'] . "'
                                                                            data-evento-descricao='" . htmlspecialchars($evento['descricao']) . "'
                                                                            data-evento-status='" . $evento['status'] . "'>
                                                                        <i class='bi bi-pencil'></i>
                                                                    </button>
                                                                    <a href='?page=calendario&month=$month&year=$year&excluir_evento=" . 
                                                                    $evento['id'] . "' 
                                                                    class='btn btn-danger btn-sm'
                                                                    onclick='return confirm(\"Tem certeza que deseja excluir este evento?\")'>
                                                                        <i class='bi bi-trash'></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>";
                                            }
                                            $calendar .= "
                                                        </div>
                                                        <div class='modal-footer'>
                                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Fechar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>";
                                        }
                                        
                                        $calendar .= "</div>";
                                        $currentDay++;
                                    }
                                    $calendar .= "</td>";
                                }
                                $calendar .= "</tr>";
                                
                                if ($currentDay > $lastDayOfMonth) {
                                    break;
                                }
                            }
                            
                            echo $calendar;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lista de Eventos -->
        <div class="tab-pane fade" id="events" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lista de Eventos</h5>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="mb-3">
                        <input type="hidden" name="page" value="calendario">
                        <input type="hidden" name="month" value="<?php echo $month; ?>">
                        <input type="hidden" name="year" value="<?php echo $year; ?>">
                        <input type="hidden" name="tab" value="events">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="todos" <?php echo $status == 'todos' ? 'selected' : ''; ?>>Todos</option>
                                <option value="pendente" <?php echo $status == 'pendente' ? 'selected' : ''; ?>>Pendentes</option>
                                <option value="realizado" <?php echo $status == 'realizado' ? 'selected' : ''; ?>>Realizados</option>
                                <option value="cancelado" <?php echo $status == 'cancelado' ? 'selected' : ''; ?>>Cancelados</option>
                            </select>
                        </div>
                    </form>

                    <!-- Lista de Eventos -->
                    <div class="eventos-lista" style="max-height: 500px; overflow-y: auto;">
                        <?php foreach ($listaEventos as $evento): ?>
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title"><?php echo htmlspecialchars($evento['descricao']); ?></h6>
                                            <p class="card-text">
                                                <i class="bi bi-calendar-date"></i> 
                                                <?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?>
                                                <br>
                                                <i class="bi bi-clock"></i> 
                                                <?php echo date('H:i', strtotime($evento['hora'])); ?>
                                                <br>
                                                <span class="badge <?php 
                                                    echo $evento['status'] == 'realizado' ? 'bg-success' : 
                                                        ($evento['status'] == 'cancelado' ? 'bg-danger' : 'bg-warning'); 
                                                ?>">
                                                    <?php echo ucfirst($evento['status']); ?>
                                                </span>
                                            </p>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-sm me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editEventoModal"
                                                    data-evento-id="<?php echo $evento['id']; ?>"
                                                    data-evento-data="<?php echo $evento['data_evento']; ?>"
                                                    data-evento-hora="<?php echo $evento['hora']; ?>"
                                                    data-evento-descricao="<?php echo htmlspecialchars($evento['descricao']); ?>"
                                                    data-evento-status="<?php echo $evento['status']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php echo generateDeleteButton($evento['id'], $month, $year); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($listaEventos)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-calendar-x fs-2"></i>
                                <p class="mt-2">Nenhum evento encontrado</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para adicionar evento -->
<div class="modal fade" id="addEventoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Novo Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Data do Evento</label>
                        <input type="date" name="data_evento" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="adicionar_evento" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar evento -->
<div class="modal fade" id="editEventoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="evento_id" id="edit_evento_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Data do Evento</label>
                        <input type="date" name="data_evento" id="edit_data_evento" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hora</label>
                        <input type="time" name="hora" id="edit_hora" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" id="edit_descricao" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="pendente">Pendente</option>
                            <option value="realizado">Realizado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="editar_evento" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este evento?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" class="btn btn-danger" id="confirmDeleteBtn">Excluir</a>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-day {
    min-width: 120px;
}
.calendar-day:hover {
    background-color: rgba(0,0,0,0.05);
}
.day-number {
    font-weight: bold;
}
.today {
    background-color: #0d6efd;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.eventos-lista::-webkit-scrollbar {
    width: 6px;
}
.eventos-lista::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.eventos-lista::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}
.eventos-lista::-webkit-scrollbar-thumb:hover {
    background: #555;
}
.alert.fade-out {
    transition: opacity 1s ease-out;
    opacity: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fade out da mensagem de sucesso
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        setTimeout(function() {
            const alert = bootstrap.Alert.getOrCreateInstance(successMessage);
            successMessage.classList.add('fade-out');
            setTimeout(function() {
                alert.close();
            }, 1000);
        }, 10000);
    }

    // Configurar o modal de edição
    var editEventoModal = document.getElementById('editEventoModal');
    if (editEventoModal) {
        editEventoModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var eventoId = button.getAttribute('data-evento-id');
            var eventoData = button.getAttribute('data-evento-data');
            var eventoHora = button.getAttribute('data-evento-hora');
            var eventoDescricao = button.getAttribute('data-evento-descricao');
            var eventoStatus = button.getAttribute('data-evento-status');

            document.getElementById('edit_evento_id').value = eventoId;
            document.getElementById('edit_data_evento').value = eventoData;
            document.getElementById('edit_hora').value = eventoHora;
            document.getElementById('edit_descricao').value = eventoDescricao;
            document.getElementById('edit_status').value = eventoStatus;
        });
    }

    // Ativar a aba correta com base no parâmetro da URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab === 'events') {
        const eventsTab = document.getElementById('events-tab');
        const eventsPane = document.getElementById('events');
        const calendarTab = document.getElementById('calendar-tab');
        const calendarPane = document.getElementById('calendar');
        
        if (eventsTab && eventsPane && calendarTab && calendarPane) {
            eventsTab.classList.add('active');
            eventsPane.classList.add('show', 'active');
            calendarTab.classList.remove('active');
            calendarPane.classList.remove('show', 'active');
        }
    }

    // Configuração do modal de confirmação de exclusão
    const confirmDeleteModal = document.getElementById('confirmDeleteModal');
    if (confirmDeleteModal) {
        const modal = new bootstrap.Modal(confirmDeleteModal);
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        
        // Função para configurar o link de exclusão
        window.configureDeleteModal = function(deleteUrl) {
            confirmDeleteBtn.href = deleteUrl;
            modal.show();
        };
    }
});
</script>

<!-- Atualizar os botões de exclusão -->
<?php
// Função auxiliar para gerar o botão de exclusão
function generateDeleteButton($eventoId, $month, $year) {
    return '<button type="button" class="btn btn-danger btn-sm" onclick="configureDeleteModal(\'?page=calendario&month=' . $month . '&year=' . $year . '&excluir_evento=' . $eventoId . '\')">
        <i class="bi bi-trash"></i>
    </button>';
}
?> 