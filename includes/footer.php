    </div>
</main>

<footer class="rodape">
    <div class="container rodape__grade">
        <div>
            <h4>Milka Mics</h4>
            <p>Peças artesanais feitas à mão com carinho: brincos, cerâmica e crochê.</p>
        </div>
        <div>
            <h4>Categorias</h4>
            <a href="<?= url('produtos.php?categoria=brincos') ?>">Brincos</a>
            <a href="<?= url('produtos.php?categoria=ceramica') ?>">Cerâmica</a>
            <a href="<?= url('produtos.php?categoria=croche') ?>">Crochê</a>
        </div>
        <div>
            <h4>Atendimento</h4>
            <p>contato@milkamics.com</p>
            <p>@milkamics</p>
        </div>
    </div>
    <div class="rodape__base">
        © <?= date('Y') ?> Milka Mics — Todos os direitos reservados.
    </div>
</footer>

<script src="<?= url('assets/js/script.js') ?>"></script>
</body>
</html>