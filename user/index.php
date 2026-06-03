<?php
// index.php (Fica na raiz do projeto)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver logado, redireciona direto para a home do sistema
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: user/home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L-Essense | Alta Gastronomia</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased min-h-screen flex flex-col justify-between">

    <nav class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between border-b border-stone-200/60 sticky top-0 bg-stone-50/80 backdrop-blur-md z-50">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 bg-amber-600 rounded-xl flex items-center justify-center text-white font-serif italic font-bold text-xl shadow-md shadow-amber-600/20">L</div>
            <span class="font-serif text-2xl text-stone-900 tracking-tight font-medium">L-Essense</span>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="../user/login.php" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-black uppercase tracking-widest text-[10px] rounded-xl transition-all shadow-md shadow-amber-600/10 active:scale-95 flex items-center gap-2">
                Acessar Menu <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </nav>

    <header class="relative bg-stone-950 text-white overflow-hidden min-h-[550px] flex items-center justify-center">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat transform scale-105 transition-transform duration-1000" style="background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1600');"></div>
        
        <div class="absolute inset-0 bg-gradient-to-r from-stone-950/95 via-stone-900/80 to-transparent sm:bg-gradient-to-t sm:from-stone-950 sm:via-stone-950/50 sm:to-stone-900/30"></div>
        
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center py-20 sm:py-28">
            <div class="inline-flex items-center gap-2 bg-amber-500/10 border border-amber-400/20 px-3 py-1 rounded-full mb-6 backdrop-blur-sm">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-400 block">Experiência Gastronômica Única</span>
            </div>
            
            <h1 class="font-serif text-5xl sm:text-7xl font-medium text-stone-50 mb-6 leading-[1.1]">
                A essência do sabor, <br><span class="italic text-amber-400">servida com paixão.</span>
            </h1>
            
            <p class="max-w-2xl mx-auto text-stone-300 font-medium text-sm sm:text-base mb-10 leading-relaxed drop-shadow-sm">
                Descubra pratos que combinam frescor absoluto, cores vibrantes e receitas exclusivas feitas para marcar momentos inesquecíveis.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="../user/login.php" class="w-full sm:w-auto px-8 py-4 bg-amber-500 hover:bg-amber-600 text-stone-950 font-black uppercase tracking-widest text-[11px] rounded-xl transition-all shadow-lg shadow-amber-500/20 active:scale-95 text-center">
                    Ver Cardápio & Pedir
                </a>
                <a href="#historia" class="w-full sm:w-auto px-8 py-4 bg-white/10 border border-white/20 text-white font-black uppercase tracking-widest text-[11px] rounded-xl hover:bg-white/20 transition-all active:scale-95 text-center backdrop-blur-sm">
                    Conhecer Nossa História
                </a>
            </div>
        </div>
    </header>

    <main id="historia" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600 block mb-1">Desde 2024</span>
                    <h2 class="font-serif text-4xl sm:text-5xl text-stone-950">O Legado de Cozinhar com Alma</h2>
                </div>
                
                <p class="text-stone-600 text-sm sm:text-base leading-relaxed font-medium">
                    O <strong>L-Essense</strong> nasceu do desejo de retornar às origens da gastronomia, onde o tempo, o respeito ao produtor e o frescor dos alimentos ditam o ritmo da cozinha. Fundado por entusiastas da culinária na Universidade Positivo, o restaurante foi projetado como uma extensão de casa: acolhedor, autêntico e inesquecível.
                </p>
                
                <p class="text-stone-600 text-sm sm:text-base leading-relaxed font-medium">
                    Nossa filosofia é simples: extrair a <em>essência</em> de cada ingrediente sem camuflá-lo. Cada prato que sai de nossa cozinha conta uma história de dedicação artesanal, desde a seleção dos grãos e temperos até a finalização estética na mesa. 
                </p>

                <div class="pt-4 border-t border-stone-200 flex items-center gap-6">
                    <div class="flex flex-col">
                        <span class="font-serif text-3xl font-bold text-amber-600">100%</span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-stone-400">Ingredientes Orgânicos</span>
                    </div>
                    <div class="w-px h-10 bg-stone-200"></div>
                    <div class="flex flex-col">
                        <span class="font-serif text-3xl font-bold text-stone-950">Artisan</span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-stone-400">Produção Local</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5 relative">
                <div class="aspect-[4/5] bg-stone-200 rounded-3xl overflow-hidden shadow-xl border border-stone-200/60 relative">
                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?q=80&w=800" alt="Prato pronto L-Essense" class="w-full h-full object-cover hover:scale-105 transition-all duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-stone-950/40 to-transparent"></div>
                </div>
                
                <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-2xl shadow-lg border border-stone-100 max-w-[180px] hidden sm:block">
                    <p class="text-xs font-semibold text-stone-900 leading-snug">"Uma experiência sensorial completa do início ao fim."</p>
                    <span class="text-[9px] font-black uppercase text-amber-600 tracking-wider block mt-2">— Crítica Gastronômica</span>
                </div>
            </div>

        </div>
    </main>

    <footer class="bg-white border-t border-stone-200 pb-10 pt-12">
        <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <div class="flex flex-col items-center md:items-start">
                <span class="font-serif text-xl italic text-stone-900 mb-1">L-Essense</span>
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-stone-400">
                        Visitação Aberta ao Público
                    </span>
                </div>
            </div>
            <div class="md:text-right">
                <p class="text-xs text-stone-400 font-medium">
                    &copy; <?php echo date("Y"); ?> Restaurante L-Essense. Todos os direitos reservados.
                </p>
                <p class="text-[9px] font-black uppercase tracking-widest text-stone-300 mt-0.5">
                    Desenvolvido pelos alunos da Universidade Positivo
                </p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>