<?php
if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {

$data = array(
    array('category_id' => 1, 'parent_id' => 0, 'name' => 'Food'),  
    array('category_id' => 2, 'parent_id' => 0, 'name' => 'Drinks'),
    array('category_id' => 3, 'parent_id' => 2, 'name' => 'Beer'), 
    array('category_id' => 4, 'parent_id' => 2, 'name' => 'Wine'),
    array('product_id' => 1, 'parent_id' => 3, 'name' => 'Kokanee'),
    array('product_id' => 2, 'parent_id' => 3, 'name' => 'Labatt'),
    array('product_id' => 3, 'parent_id' => 4, 'name' => 'House Wine'),
    array('product_id' => 4, 'parent_id' => 1, 'name' => 'Burger'),
    array('product_id' => 5, 'parent_id' => 2, 'name' => 'Water')
);

$categories = array();
$products = array();
$results = array();

// Separate categories and products
foreach($data as $entry) {
    if(isset($entry['category_id'])) {
        $entry['children'] = array();
        array_push($categories, $entry);
    } elseif(isset($entry['product_id'])) {
        array_push($products, $entry);
    }
}

// Build Tree
while(true) {
    $old_categories = $categories;

    foreach($categories as $cat_index => $category) {
        $has_children = false;
        $parent_index = -1;

        foreach($categories as $cat_index2 => $category2) {
            if($category2['parent_id'] === $category['category_id']) {
                $has_children = true;
                break;
            }

            if($category2['category_id'] === $category['parent_id']) {
                $parent_index = $cat_index2;
            }
        }

        if(!$has_children) {
            if($parent_index !== -1) { 
                // Append products to category
                foreach($products as $product) {
                    if($product['parent_id'] == $category['category_id']) {
                        $category['children'][] = $product;
                    }
                }

                $categories[$parent_index]['children'][] = $category;
                unset($categories[$cat_index]);
                break;
            }
        }
    }

    if(count($old_categories) === count($categories)) {
        break;
    } 
}

$categories = array_values($categories);
// Append products to root categories
foreach($categories as $index => $category) {
    foreach($products as $product) {
        if($product['parent_id'] == $category['category_id']) {
            $categories[$index]['children'][] = $product;
        }
    }
}

echo json_encode(($categories));
exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tree Building</title>
    <meta charset="utf-8">
</head>
<style>
    li {
        list-style: none;
        cursor: pointer;
        -webkit-user-select: none;  
        -moz-user-select: none;    
        -ms-user-select: none;    
        user-select: none;       
    }
    
    li.category {
        font-weight: bold;
    }
    
    span:after {
        content: "- ";
    }

    span.expanded:after {
        content: "+ ";
    }

    
    
</style>

<body>
    <script src="https://code.jquery.com/jquery-3.1.0.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
    <script>
        var url = "index.php";
        $.ajax(url, {
            method: "GET",
            success: function(response) {
                var data = JSON.parse(response);

                var $categories_list = $("<ul></ul>");
                var root = "<li id='root' class='category'><span class='expanded'></span>Root</li><ul></ul>";
                $categories_list.append(root);

                $("body").append($categories_list);
                
                for(var ii = 0; ii < data.length; ii++) {
                    if(data[ii].hasOwnProperty('category_id')) {
                        var $category_list_item = $("<li></li>");
                        var $category_children_list = $("<ul></ul>");

                        $category_list_item.addClass('category');
                        $category_list_item.attr("id", "cat_" + data[ii].category_id);

                        $category_list_item.append("<span class='expanded'></span>" + data[ii].name);
                        $("#root").next("ul").append($category_list_item);
                        $category_list_item.after($category_children_list);

                        var next = data[ii].children;
                        var counter = 0;

                        while(next.length !== 0) {
                            var current = next;
                            next = [];
                            for(var jj=0; jj < current.length; jj++) {
                                var parent_id = current[jj].parent_id;
                                var $new_category_list_item = $("<li></li>");

                                if(current[jj].hasOwnProperty('category_id')) {
                                    $new_category_list_item.addClass('category');
                                    $new_category_list_item.attr("id", "cat_" + current[jj].category_id);
                                    $new_category_list_item.append("<span class='expanded'></span>" + current[jj].name);

                                    for(var child in current[jj].children) {
                                        next.push(current[jj].children[child]);
                                    }

                                } else {
                                    $new_category_list_item.append("# " + current[jj].name);
                                }

                                // Display item on tree
                                $("#cat_" + current[jj].parent_id).next("ul").append($new_category_list_item);
                                
                                if(current[jj].hasOwnProperty('category_id')) {
                                    $("#cat_" + current[jj].category_id).after("<ul></ul>");
                                }
                            }
                        }
                    }
                }

                $(".category").on("click", function() {
                    $(this).next("ul").fadeToggle();
                    $(this).children("span").toggleClass("expanded");
                });
            }
        });
    </script>
</body>
</html>

