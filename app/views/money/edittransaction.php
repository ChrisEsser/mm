<?php

/** @var Transaction $transaction */
$transaction = $this->getVar('transaction');
/** @var Category[] $categories */
$categories = $this->getVar('categories');

?>

<h1 class="page_header"><?=($transaction->transaction_id) ? 'Edit' : 'Create'?> Transaction</h1>

<form method="POST" action="/money/transactions/save">

    <input type="hidden" name="transaction" value="<?=$transaction->transaction_id?>">

    <div class="row">

        <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label" for="title">Title</label>
                <input type="text" class="form-control" name="title" value="<?=$transaction->title?>" />
            </div>
        </div>

        <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label" for="merchant">Merchant</label>
                <input type="text" class="form-control" name="merchant" value="<?=$transaction->merchant?>" />
            </div>
        </div>

    </div>

    <div class="row">

        <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label" for="title">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-currency-dollar"></i>
                    </span>
                    <input type="number" step="0.01" class="form-control" name="amount" value="<?=$transaction->amount?>" />
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label" for="merchant">Date</label>
                <input type="text" class="form-control" name="date" value="<?=$transaction->date?>" />
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="mb-3">
                <label class="form-label" for="category_id">Category</label>
                <select class="form-control" name="category_id">
                    <option value="0">- Select --</option>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?=$category->category_id?>" <?=($category->category_id == $transaction->category_id) ? 'selected' : ''?>><?=$category->primary_desc?> | <?=$category->detail_desc?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="copy_merchant" id="copy_merchant" value="1">
        <label class="form-check-label" for="copy_merchant">
            Copy all other existing and future transactions from this merchant to this category
        </label>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="copy_title" id="copy_title" value="1">
        <label class="form-check-label" for="copy_title">
            Copy all other existing and future transactions with a similar title to this category
        </label>
    </div>


    <button class="btn btn-lg btn-success" type="submit"><i class="bi bi-check-circle"></i>&nbsp;Save</button>

</form>

<?php
//
//function calculateSimilarity($string1, $string2) {
//
//    $length1 = mb_strlen($string1);
//    $length2 = mb_strlen($string2);
//    $maxWeight = max($length1, $length2);
//    $totalWeight = 0;
//    $matchingWeight = 0;
//
//    for ($i = 0; $i < $maxWeight; $i++) {
//        if (isset($string1[$i]) && isset($string2[$i])) {
//            $char1 = mb_substr($string1, $i, 1);
//            $char2 = mb_substr($string2, $i, 1);
//
//            if ($char1 === $char2) {
//                $matchingWeight += 2; // Characters match, increase weight
//            } elseif (strtolower($char1) === strtolower($char2)) {
//                $matchingWeight += 1; // Case-insensitive match, increase weight
//            }
//        }
//
//        $totalWeight += 2; // Increment total weight for each character
//    }
//
//    $similarity = $matchingWeight / $totalWeight;
//
//    return round($similarity * 100, 2);
//}
//
//
//$string1 = "lily";
//$string2 = "Lily ";
//$similarity = levenshtein($string1, $string2);
//
//echo "Similarity: " . $similarity;

?>


