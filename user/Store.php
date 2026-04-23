<?php

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante - Painel Principal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:italic&family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Instrument Serif', serif; }

    /* Animação de transição das imagens do banner */
    @keyframes bannerFade {
        0%, 33% { opacity: 0.4; } /* Aparece */
        40%, 100% { opacity: 0; }  /* Desaparece */
    }

    .animate-fade-1 { animation: bannerFade 15s infinite; }
    .animate-fade-2 { animation: bannerFade 15s infinite 5s; } /* Começa com 5s de atraso */
    .animate-fade-3 { animation: bannerFade 15s infinite 10s; } /* Começa com 10s de atraso */
    </style>
</head>
<body class="bg-white text-stone-900 antialiased">
    <div class="max-w-6xl mx-auto px-6">
        <?php require 'menu.php'; ?>
    </div>
</body>
</html>