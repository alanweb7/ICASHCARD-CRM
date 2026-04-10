<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css">
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<style>
    /* Estilo do contêiner do slide */
    .slider {
        position: relative;
        width: 100%;
        max-width: 800px;
        min-height: 350px;
        /* Tamanho máximo do slide */
        margin: auto;
        overflow: hidden;
    }

    /* Estilo das imagens do slide */
    .slides {
        display: flex;
        transition: transform 0.5s ease;
    }

    .slide {
        min-width: 100%;
        transition: 0.5s ease;
    }

    .slide img {
        width: 100%;
        height: auto;
    }

    /* Botões de navegação (esquerda/direita) */
    .prev,
    .next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 10px;
        cursor: pointer;
    }

    .prev {
        left: 10px;
    }

    .next {
        right: 10px;
    }
</style>
<!-- CSS de avisos -->
<style>
    /* Estilos gerais para a grid */
    .custom-grid {
        display: flex;
        flex-wrap: wrap;
        /* Permite múltiplas linhas */
        gap: 20px 0;
        /* Espaçamento entre os cards */
    }

    .col-md-4 {
        flex: 1 1 calc(50% - 20px);
        /* 3 colunas com espaço entre elas */
        box-sizing: border-box;
    }

    .card {
        background-color: #ffffff;
        /* Fundo branco */
        border: 1px solid #ddd;
        /* Borda suave */
        border-radius: 5px;
        /* Cantos arredondados */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        /* Sombra leve */
        overflow: hidden;
    }

    .card-header {
        background-color: #f5f5f5;
        /* Fundo do cabeçalho */
        padding: 10px 15px;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }

    .card-header h4 {
        margin: 0;
        font-size: 16px;
        color: #333;
    }
 
    .card-body {
        padding: 15px;
        color: #666;
        font-size: 14px;
        line-height: 1.5;
    }
</style>


<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="slider">
                <div class="slides">
                    <?php
                    $slides = [];
                    foreach ($banners as $key => $banner) {
                        // Obtém a URL da imagem
                        $img = $banner["media"];
                        $active = $banner["active"];

                        // Armazena o HTML do slide
                        if ($active) {
                            $slides[] = "<div class=\"slide\"><img src=\"{$img}\"></div>";
                        }
                    }
                    echo implode('', $slides);
                    ?>

                </div>
                <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
                <button class="next" onclick="moveSlide(1)">&#10095;</button>
            </div>
        </div>
        <!-- AREA DE AVISOS -->
        <div class="row">
            <h4 class="sub-content">Recados</h4>
        </div>
        <div class="row custom-grid">

            <?php
            foreach ($notes as $key => $note) {
                // Obtém a URL da imagem
                $active = $note["active"];

                if ($active) {

            ?>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h4><?php echo $note['title']; ?></h4>
                            </div>
                            <div class="card-body" style="min-height: 220px;">
                                <p><?php echo $note['content']; ?></p>
                            </div>
                        </div>
                    </div>

            <?php
                }
            }
            ?>

        </div>
    </div>
</div>


<script>
    // Controle do slide
    let slideIndex = 0;

    function moveSlide(step) {
        const slides = document.querySelector('.slides');
        const totalSlides = document.querySelectorAll('.slide').length;

        slideIndex += step;

        if (slideIndex >= totalSlides) {
            slideIndex = 0;
        } else if (slideIndex < 0) {
            slideIndex = totalSlides - 1;
        }

        slides.style.transform = `translateX(-${slideIndex * 100}%)`;
    }

    // Avançar automaticamente após 3 segundos
    setInterval(() => {
        moveSlide(1);
    }, 5000);
</script>

<?php init_tail(); ?>