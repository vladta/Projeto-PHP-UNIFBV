<?php
require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'criar') {
  $nome = trim($_POST['nome'] ?? '');
  $desc = trim($_POST['descricao'] ?? '');
  $foto = upload_foto('foto');

  if ($nome !== '' && $desc !== '') {
    $st = $pdo->prepare("INSERT INTO produtos (nome, descricao, foto_path) VALUES (:n,:d,:f)");
    $st->execute([':n'=>$nome, ':d'=>$desc, ':f'=>$foto]);
    header("Location: produtos.php"); exit;
  }
}


if (($_GET['acao'] ?? '') === 'del' && isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $st = $pdo->prepare("SELECT foto_path FROM produtos WHERE id=:id");
  $st->execute([':id'=>$id]);
  if ($row = $st->fetch()) {
    if ($row['foto_path'] && file_exists($row['foto_path'])) @unlink($row['foto_path']);
  }
  $pdo->prepare("DELETE FROM produtos WHERE id=:id")->execute([':id'=>$id]);
  header("Location: produtos.php"); exit;
}


$lista = $pdo->query("SELECT * FROM produtos ORDER BY id DESC")->fetchAll();


$edit = null;
if (($_GET['acao'] ?? '') === 'edit' && isset($_GET['id'])) {
  $st = $pdo->prepare("SELECT * FROM produtos WHERE id=:id");
  $st->execute([':id'=>(int)$_GET['id']]);
  $edit = $st->fetch();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
  $id   = (int)$_POST['id'];
  $nome = trim($_POST['nome'] ?? '');
  $desc = trim($_POST['descricao'] ?? '');
  $fotoNova = upload_foto('foto');

  if ($nome !== '' && $desc !== '') {
    if ($fotoNova) {
      $st = $pdo->prepare("SELECT foto_path FROM produtos WHERE id=:id");
      $st->execute([':id'=>$id]);
      if ($row = $st->fetch()) {
        if ($row['foto_path'] && file_exists($row['foto_path'])) @unlink($row['foto_path']);
      }
      $sql = "UPDATE produtos SET nome=:n, descricao=:d, foto_path=:f WHERE id=:id";
      $args = [':n'=>$nome, ':d'=>$desc, ':f'=>$fotoNova, ':id'=>$id];
    } else {
      $sql = "UPDATE produtos SET nome=:n, descricao=:d WHERE id=:id";
      $args = [':n'=>$nome, ':d'=>$desc, ':id'=>$id];
    }
    $pdo->prepare($sql)->execute($args);
    header("Location: produtos.php"); exit;
  }
}
?>
<?php include __DIR__ . '/header.php'; ?>

<h2>Produtos (CRUD)</h2>

<h3>Criar novo</h3>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="acao" value="criar">
  <p>Nome:<br><input type="text" name="nome" required></p>
  <p>Descrição:<br><textarea name="descricao" rows="5" cols="60" required></textarea></p>
  <p>Foto: <input type="file" name="foto" accept="image/*"></p>
  <p><button type="submit">Salvar</button></p>
</form>

<?php if ($edit): ?>
<hr>
<h3>Editar produto #<?= e($edit['id']) ?></h3>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="acao" value="atualizar">
  <input type="hidden" name="id" value="<?= e($edit['id']) ?>">
  <p>Nome:<br><input type="text" name="nome" value="<?= e($edit['nome']) ?>" required></p>
  <p>Descrição:<br><textarea name="descricao" rows="5" cols="60" required><?= e($edit['descricao']) ?></textarea></p>
  <p>Foto (enviar nova para substituir): <input type="file" name="foto" accept="image/*"></p>
  <?php if ($edit['foto_path']): ?>
    <p>Atual:<br><img src="<?= e($edit['foto_path']) ?>" width="200"></p>
  <?php endif; ?>
  <p><button type="submit">Atualizar</button></p>
</form>
<?php endif; ?>

<hr>
<h3>Lista</h3>
<?php if (!$lista): ?>
  <p>Sem produtos cadastrados.</p>
<?php else: ?>
<table border="1" cellpadding="6" cellspacing="0">
  <tr>
    <th>ID</th><th>Nome</th><th>Descrição</th><th>Foto</th><th>Ações</th>
  </tr>
  <?php foreach ($lista as $r): ?>
  <tr>
    <td><?= e($r['id']) ?></td>
    <td><?= e($r['nome']) ?></td>
    <td><?= nl2br(e($r['descricao'])) ?></td>
    <td><?php if ($r['foto_path']): ?><img src="<?= e($r['foto_path']) ?>" width="120"><?php endif; ?></td>
    <td>
      <a href="produtos.php?acao=edit&id=<?= e($r['id']) ?>">Editar</a> |
      <a href="produtos.php?acao=del&id=<?= e($r['id']) ?>" onclick="return confirm('Apagar?');">Excluir</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
