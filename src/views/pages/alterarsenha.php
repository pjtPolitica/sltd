<?php $render('header',['css'=> 'alterarSenha']); ?>

<nav>
    <div><a href="<?=$base;?>/">Início</a></div>
    <div><a href="<?=$base;?>/jogo">Jogar</a></div>
    <div><a href="<?=$base;?>/rankingindividual">Meu Ranking</a></div>
</nav>

<div id="titulo">Alterar Senha</div>
<div id="alerta">
    <?php if(!empty($flash)): ?>
        <?php echo $flash; ?>
    <?php endif; ?>
</div>
<section id="senha">
    <section id="perfil">
        <div id="imagem"><img src="<?=$base;?>/img/avatar/<?=$pessoa->avatar;?>" alt="Avatar" class="avatar"></div>
        <div class="apelido"><?=$pessoa->nome;?></div>
    </section>
    <form action="" method="post">
        <input type="password" name="senha1" placeholder="Digite sua nova senha">
        <input type="password" name="senha2" placeholder="Repita a nova senha">
        <button>Salvar</button>
    </form>
</section>

<?php $render('footer'); ?>