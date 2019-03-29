<div class="itemGrain">
    <?php
    echo $this->Catalog->membershipLinks();
    echo $this->FgHtml->tag('h3', $grain['Item']['name']);
    echo $this->FgHtml->tag('p', $grain['Item']['description']);
    ?>
</div>