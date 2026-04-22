<footer class="mt-24 pb-12 border-t border-stone-100">
    <div class="max-w-6xl mx-auto pt-12 flex flex-col md:flex-row justify-between items-center gap-6">
        
        <div class="flex flex-col items-center md:items-start">
            <span class="font-serif text-2xl italic text-stone-800 mb-2">L-Essense</span>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-stone-400">
                    Sistema Online 
                    <span class="mx-2 text-stone-200">|</span> 
                    <?php echo $_SESSION['perfil'] === 'admin' ? 'Acesso Gestor' : 'Acesso Cliente'; ?>
                </span>
            </div>
        </div>

        <div class="text-center md:text-right">
            <p class="text-xs text-stone-400 font-medium mb-1">
                &copy; <?php echo date("Y"); ?> Restaurante L-Essense. Todos os direitos reservados.
            </p>
            <p class="text-[9px] font-black uppercase tracking-widest text-stone-300">
                Desenvolvido pelos alunos da Universidade Positivo
            </p>
        </div>
    </div>
</footer>

<script>
    // Garante que qualquer ícone Lucide no rodapé seja carregado
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>