<?php
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar') {
  $titulo   = trim($_POST['titulo'] ?? '');
  $historia = trim($_POST['historia'] ?? '');
  $foto     = upload_foto('foto');

  if ($titulo !== '' && $historia !== '') {
    $st = $pdo->prepare("INSERT INTO quem_somos (titulo, historia, foto_path) VALUES (:t,:h,:f)");
    $st->execute([':t'=>$titulo, ':h'=>$historia, ':f'=>$foto]);
    header("Location: quem_somos.php"); exit;
  }
}


if (($_GET['acao'] ?? '') === 'del' && isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $st = $pdo->prepare("SELECT foto_path FROM quem_somos WHERE id=:id");
  $st->execute([':id'=>$id]);
  if ($row = $st->fetch()) {
    if ($row['foto_path'] && file_exists($row['foto_path'])) @unlink($row['foto_path']);
  }
  $pdo->prepare("DELETE FROM quem_somos WHERE id=:id")->execute([':id'=>$id]);
  header("Location: quem_somos.php"); exit;
}


$lista = $pdo->query("SELECT * FROM quem_somos ORDER BY id DESC")->fetchAll();


$edit = null;
if (($_GET['acao'] ?? '') === 'edit' && isset($_GET['id'])) {
  $st = $pdo->prepare("SELECT * FROM quem_somos WHERE id=:id");
  $st->execute([':id'=>(int)$_GET['id']]);
  $edit = $st->fetch();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
  $id       = (int)$_POST['id'];
  $titulo   = trim($_POST['titulo'] ?? '');
  $historia = trim($_POST['historia'] ?? '');
  $fotoNova = upload_foto('foto');

  if ($titulo !== '' && $historia !== '') {
    if ($fotoNova) {
      
      $st = $pdo->prepare("SELECT foto_path FROM quem_somos WHERE id=:id");
      $st->execute([':id'=>$id]);
      if ($row = $st->fetch()) {
        if ($row['foto_path'] && file_exists($row['foto_path'])) @unlink($row['foto_path']);
      }
      $sql = "UPDATE quem_somos SET titulo=:t, historia=:h, foto_path=:f WHERE id=:id";
      $args = [':t'=>$titulo, ':h'=>$historia, ':f'=>$fotoNova, ':id'=>$id];
    } else {
      $sql = "UPDATE quem_somos SET titulo=:t, historia=:h WHERE id=:id";
      $args = [':t'=>$titulo, ':h'=>$historia, ':id'=>$id];
    }
    $pdo->prepare($sql)->execute($args);
    header("Location: quem_somos.php"); exit;
  }
}
?>
<?php include __DIR__ . '/header.php'; ?>

<h2>Quem Somos (CRUD)</h2>

<h3>Criar novo</h3>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="acao" value="criar">
  <p>Título:<br><input type="text" name="titulo" required></p>
  <p>História:<br><textarea name="historia" rows="6" cols="60" required></textarea></p>
  <p>Foto: <input type="file" name="foto" accept="image/*"></p>
  <p><button type="submit">Salvar</button></p>
</form>

<?php if ($edit): ?>
<hr>
<h3>Editar registro #<?= e($edit['id']) ?></h3>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="acao" value="atualizar">
  <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
  <p>Título:<br><input type="text" name="titulo" value="<?= e($edit['titulo']) ?>" required></p>
  <p>História:<br><textarea name="historia" rows="6" cols="60" required><?= e($edit['historia']) ?></textarea></p>
  <p>Foto (enviar nova para substituir): <input type="file" name="foto" accept="image/*"></p>
  <?php if ($edit['foto_path']): ?>
    <p>Atual:<br><img src="<?= e($edit['foto_path']) ?>" alt="" width="200"></p>
  <?php endif; ?>
  <p><button type="submit">Atualizar</button></p>
</form>
<?php endif; ?>

<hr>
<h3>Lista</h3>
<?php if (!$lista): ?>
  <p>Sem registros.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
  <tr>
    <th>ID</th><th>Título</th><th>História</th><th>Foto</th><th>Ações</th>
  </tr>
  <?php foreach ($lista as $r): ?>
  <tr>
    <td><?= e($r['id']) ?></td>
    <td><?= e($r['titulo']) ?></td>
    <td><?= nl2br(e($r['historia'])) ?></td>
    <td><?php if ($r['foto_path']): ?><img src="<?= e($r['foto_path']) ?>" width="120"><?php endif; ?></td>
    <td>
      <a href="quem_somos.php?acao=edit&id=<?= e($r['id']) ?>">Editar</a> |
      <a href="quem_somos.php?acao=del&id=<?= e($r['id']) ?>" onclick="return confirm('Apagar?');">Excluir</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>