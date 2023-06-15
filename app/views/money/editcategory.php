<?php

/** @var Category $category */
$category = $this->getVar('category');

?>

<h1 class="page_header"><?=($category->category_id) ? 'Edit' : 'Create'?> Category</h1>

<form method="POST" action="/money/categories/save">

    <input type="hidden" name="category" value="<?=$category->category_id?>">

    <div class="row">

        <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label" for="primary_desc">Primary Description</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="primary_desc" value="<?=$category->primary_desc?>" />
                    <span class="input-group-text" data-bs-toggle="tooltip" data-bs-placement="top" title="You can use the same text here as other categories use. this might come in handy for grouping in reports later">
                        <i class="bi bi-info-circle"></i>
                    </span>
                </div>

            </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label" for="primary_desc">Detail Description</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="detail_desc" value="<?=$category->detail_desc?>" />
                    <span class="input-group-text" data-bs-toggle="tooltip" data-bs-placement="top" title=" This is the maid description used to identify a category. This value has to be unique to this category.">
                        <i class="bi bi-info-circle"></i>
                    </span>
                </div>
            </div>
        </div>

    </div>

    <button class="btn btn-lg btn-success" type="submit"><i class="bi bi-check"></i>&nbsp;Save</button>

</form>

<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
