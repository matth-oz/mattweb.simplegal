<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true){die();}

$this->addExternalJS('/local/js/glidejs/glide.min.js');
$this->addExternalCss('/local/js/glidejs/glide.core.min.css');

if($arParams['USE_ADDITIONAL_CSS'] == 'Y'){
    $this->addExternalCss('/local/js/glidejs/glide.theme.min.css');
}
?>
<div class="slider-wrap">
    <?if(!empty($arResult["ERRORS"])):?>
        <div class="slider-comp-errors">
            <?=implode("<br />", $arResult["ERRORS"])?>
        </div>
    <?endif?>
    <?if(!empty($arResult["ITEMS"])):?>
        <div class="glide">
            <div class="glide__track" data-glide-el="track">
                <ul class="glide__slides">
                <?foreach($arResult["ITEMS"] as $arSlide):?>
                    <li class="glide__slide">
                        <img src="<?=$arSlide['PREV_PICT']['SRC']?>" />
                    </li>
                <?endforeach?>
                </ul>
            </div>

            <?if($arParams['USE_NAV_POINTS'] == 'Y'):?>
            <div class="nav-points" data-glide-el="controls[nav]">
                <?for($i=0; $i <= count($arResult['ITEMS']) - 1; $i++):?>
                <button class="nav-point" data-glide-dir="=<?=$i?>"></button>
                <?endfor?>
            </div>
            <?endif?>
            <?if($arParams['USE_NAV_CONTROLS'] == 'Y'):?>
            <div class="glide__arrows" data-glide-el="controls">
                <button class="glide__arrow glide__arrow--left" data-glide-dir="<">←</button>
                <button class="glide__arrow glide__arrow--right" data-glide-dir=">">→</button>
            </div>
            <?endif?>
        </div>
    <?endif?>
</div>
<?if(!empty($arResult["ITEMS"])):?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        let sliderParams = {
            type: '<?=$arParams['MOVEMENT_TYPE']?>',
            perView: <?=$arParams['SLIDES_PER_VIEW']?>,
            focusAt: <?=$arParams['FOCUS_AT_POSITION']?>,
            gap: <?=$arParams['GAP_SIZE']?>,
            autoplay: <?if($arParams['USE_AUTOPLAY'] === false):?>false<?else:?><?=$arParams['USE_AUTOPLAY']?><?endif?>,
            direction: '<?=$arParams['DIRECTION_TYPE']?>',
        }

       const glide = new Glide('.glide', sliderParams);
       glide.mount();
    });
</script>
<?endif?>