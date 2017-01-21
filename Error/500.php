<?php isset($phrase) ?: $phrase = ''?>
<style>
div.error_user_mmessage {
    font-size: 120%;
    font-family: monospace;
    text-align: center;
    border-radius: 5px;
    margin: 0 auto;
    max-width: 20em;
}

div.error_user_mmessage div.error_header {
    font-size: 110%;
    font-weight: 500;
    padding: 5px;
    color: #fff;
    border-radius: 5px 5px 0 0;
    background-color: #00bc09;
}

div.error_user_mmessage div.error_text {
    padding: 5px 15px;
    font-family: sans-serif;
    background-color: #aaa;
    color: #fff;
    border-radius: 0 0 5px 5px;
    text-shadow: 2px 2px 7px rgba(0, 0, 0, 0.4), 0 0 1px #555;
}

</style>
<div class="error_user_mmessage">
    <div class="error_header"><?php echo $statusCode.' '.$phrase ?></div>
    <div></div>
    <div class="error_text">
        <?php
        foreach ($message as $MessageValue) {
            echo $MessageValue.'<br>';
        }
        ?>
    </div>
</div>