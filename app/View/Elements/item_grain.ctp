<div class="itemGrain">
    <?php
    echo $this->Catalog->membershipLinks();
    echo $this->Html->tag('h3', $grain['Item']['name']);
    echo $this->Html->tag('p', $grain['Item']['description']);
    ?>
</div>