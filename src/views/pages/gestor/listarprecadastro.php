<?php $render('header', ['css'=> 'gestorHome']); ?>

<div id="titulo">Inativar Pré-cadastro</div>

<div id="btn">
    <div><a href="<?= $base;?>/gestor">Início</a></div>
</div>

<form action="" method="post">
    <input type="text" name="pesquisa" placeholder="Digite o Nome do Aluno">
    <button type="submit"><img src="<?=$base;?>/img/pesquisar.png" alt="Pesquisar"></button>
</form>

<section id="lista">
    <div class="nome">Usuário</div>
    <div class="pontos">Ação</div>
</section>
<?php if(!empty($precadastro)):?>
<?php foreach($precadastro as $precadastroItem):?>
<section class="item">
    <div class="nome">
        <div class="apelido"><?=$precadastroItem->nome;?> - <?=$precadastroItem->chave;?></div>
    </div>
    <div class="pontos"><a href="<?=$base;?>/gestor/inativarprecadastro/<?=$precadastroItem->id;?>"><?=$precadastroItem->status;?></a></div>
</section>
<?php endforeach;?>
<?php endif;?>

<?php $render('footer'); ?>