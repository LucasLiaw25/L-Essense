<?php
// user/rodape.php
?>
<footer class="mt-28 border-t border-stone-200/80 bg-white">
    <div class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pb-10 border-b border-stone-100 items-start">
            
            <div class="space-y-3">
                <span class="font-serif text-2xl italic tracking-wide text-stone-950 block">L-Essense</span>
                <p class="text-xs text-stone-400 max-w-xs leading-relaxed font-medium">
                    Uma experiência gastronômica conceitual, unindo o minimalismo clássico à alta culinária.
                </p>
            </div>

            <div class="flex flex-col md:items-center justify-center h-full pt-2 md:pt-0">
                <div class="inline-flex items-center gap-2.5 px-4 py-2 bg-stone-50 border border-stone-200/60 rounded-full shadow-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-[0.15em] text-stone-600">
                        Ambiente Seguro & Monitorado
                    </span>
                </div>
            </div>

            <div class="flex flex-col md:items-end space-y-2">
                <span class="text-[9px] font-black uppercase tracking-widest text-stone-400 block mb-1">Navegação</span>
                <div class="flex flex-wrap md:justify-end gap-x-4 gap-y-2 text-xs font-semibold text-stone-500">
                    <a href="../user/home.php" class="hover:text-stone-950 transition-colors">Início</a>
                    <a href="../dashboard/status.php" class="hover:text-stone-950 transition-colors">Meus Pedidos</a>
                    <a href="../user/perfil.php" class="hover:text-stone-950 transition-colors">Minha Conta</a>
                </div>
            </div>

        </div>

        <div class="pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2 text-xs text-stone-400 font-medium text-center sm:text-left">
                <i data-lucide="copyright" class="w-3.5 h-3.5 text-stone-300"></i>
                <span><?php echo date("Y"); ?> Boutique L-Essense. Todos os direitos reservados.</span>
            </div>
            
            <div class="flex items-center gap-1.5 bg-stone-50 border border-stone-100 rounded-lg px-3 py-1.5">
                <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-stone-400"></i>
                <p class="text-[9px] font-black uppercase tracking-wider text-stone-400">
                    Universidade Positivo
                </p>
            </div>
        </div>

    </div>
</footer>