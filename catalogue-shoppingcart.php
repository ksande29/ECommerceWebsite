<?php
// - - - - User home page info - - - - - -

require_once("./include/membersite_config.php");

if(!$fgmembersite->CheckLogin())
{
    $fgmembersite->RedirectToURL("login.php");
    exit;
}

// - - - - Datatabase info for catalogue drop down menu
//database connection
$host = "localhost";
$user = "root";
$password = "dbpass";
$db = "E_Commerce";
$tbl = "Product";
                    
$con = mysqli_connect($host, $user, $password, $db) or die(mysqli($con));

$product = $_POST['item'];
$query = "SELECT * FROM $tbl WHERE name = '" . $product . "'";
$results_array = mysqli_query($con, $query); 

// - - - - Session info for shopping cart

session_start();
require_once("dbcontroller.php");
$db_handle = new DBController();

if(!empty($_GET["action"])) 
{
    switch($_GET["action"]) 
    {
	case "add":
	if(!empty($_POST["quantity"])) 
        {
            $productByCode = $db_handle->runQuery("SELECT * FROM Product WHERE code='" . $_GET["code"] . "'");
            $itemArray = array($productByCode[0]["code"]=>array('name'=>$productByCode[0]["Name"], 'code'=>$productByCode[0]["code"], 'quantity'=>$_POST["quantity"], 'price'=>$productByCode[0]["Price"]));

            if(!empty($_SESSION["cart_item"])) 
            {
                if(in_array($productByCode[0]["code"],array_keys($_SESSION["cart_item"]))) 
                {
                    foreach($_SESSION["cart_item"] as $k => $v) 
                    {
                        if($productByCode[0]["code"] == $k) 
                        {
                            if(empty($_SESSION["cart_item"][$k]["quantity"])) 
                            {
                                $_SESSION["cart_item"][$k]["quantity"] = 0;
                            }
                            $_SESSION["cart_item"][$k]["quantity"] += $_POST["quantity"];
                        }
                    }
                } 
                else 
                {
                    $_SESSION["cart_item"] = array_merge($_SESSION["cart_item"],$itemArray);
                }
            }
            else 
            {
                $_SESSION["cart_item"] = $itemArray;
            }
        }
        break;

        case "remove":
        if(!empty($_SESSION["cart_item"])) 
        {
            foreach($_SESSION["cart_item"] as $k => $v) 
            {
                if($_GET["code"] == $k)
                    unset($_SESSION["cart_item"][$k]);				
                if(empty($_SESSION["cart_item"]))
                    unset($_SESSION["cart_item"]);
            }
        }
        break;

        case "empty":
        unset($_SESSION["cart_item"]);
        break;	
    }
}
?>

<html>
    <head>
        <title> catalogue </title>
        <link href="/CSIS2440/ECommerceSite/css/catalogue-shoppingcart-style.css" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Bangers|Cantarell|Fredoka+One|Monoton|Rock+Salt|Russo+One" rel="stylesheet">   
    </head>
    <body>
        <h1> E-commerce Website </h1>
        <div id="logout">
            <p>Welcome back <?= $fgmembersite->UserFullName(); ?>!
            <br><a href="logout.php"> logout </a>
            <br><a href="change-pwd.php"> change password </a></p>
        </div>
        <div id="container">
            <div id="products" class="main-section">
                <h2> Products </h2>
                <div id="shop-img"> <img src="img/shop.jpg"> </div>
                <div id="catalogue" class="sub-section">
                    <h3> Product Catalogue </h2>
                    <p> Please select an item to find out more about it: </p>
                    <form action="catalogue-shoppingcart.php" method="post">
                        <select name="item">
                            <?php 
                                //get all products
                                $query = "SELECT name from $tbl";
                                $return = mysqli_query($con, $query);
                                $menu = "";
                                //populate menu with results
                                while ($row = mysqli_fetch_array($return))
                                {
                                    $menu = $menu . '<option>'. $row[0].'</option>';
                                }
                                echo $menu;  
                            ?>
                        </select>
                    <input type="submit">
                    </form>
                </div>  
                <div id="product-information" class="sub-section"> 
                    <?php
                    while ($row = mysqli_fetch_array($results_array)) 
                    {
                        echo('<form method="post" action="catalogue-shoppingcart.php?action=add&code=' . $row[5] . '">');                        
                        echo('<div> <img src="img/' . $row[4] . '" > </div>');
                        echo('<h4>' . $row[1] . '</h4>');
                        echo('<div id="product-informtion-text"> <span>product number: </span> ' . $row[5]);
                        echo('<br> <span>description: </span> ' . $row[2]);
                        echo('<br> <span>price: </span>$' . $row[3] . '</div>');
                        echo('<div id="add-to-cart-input">
                            <input type="text" name="quantity" value="1" />
                            <input type="submit" value="Add to cart" class="btnAddAction" />
                            </div>
                            </form>');
                    }
                    ?>   
                </div>
                <div id="order">   
                </div>  
            </div>
        
            
            <div id="shopping-cart" class="main-section">
                <h2> Shopping Cart </h2>
                <div id="cart-img"> <img src="img/cart.jpg"> </div>
                <div class="txt-heading" id="cart-totals">Shopping Cart <a id="btnEmpty" href="catalogue-shoppingcart.php?action=empty">Empty Cart</a></div>
                    <?php
                    if(isset($_SESSION["cart_item"]))
                    {
                        $item_total = 0;
                    ?>	
                    <table cellpadding="10" cellspacing="1">
                        <tr>
                            <th style="text-align:left;"><strong>Name</strong></th>
                            <th style="text-align:left;"><strong>Code</strong></th>
                            <th style="text-align:right;"><strong>Quantity</strong></th>
                            <th style="text-align:right;"><strong>Price</strong></th>
                            <th style="text-align:center;"><strong>Action</strong></th>
                        </tr>	
                        <?php		
                            foreach ($_SESSION["cart_item"] as $item)
                            {
                        ?>
                        <tr>
                            <td style="text-align:left;border-bottom:#F0F0F0 1px solid;"><?php echo $item["name"]; ?></td>
                            <td style="text-align:left;border-bottom:#F0F0F0 1px solid;"><?php echo $item["code"]; ?></td>
                            <td style="text-align:right;border-bottom:#F0F0F0 1px solid;"><?php echo $item["quantity"]; ?></td>
                            <td style="text-align:right;border-bottom:#F0F0F0 1px solid;"><?php echo "$".$item["price"]; ?></td>
                            <td style="text-align:center;border-bottom:#F0F0F0 1px solid;"><a href="catalogue-shoppingcart.php?action=remove&code=<?php echo $item["code"]; ?>" class="btnRemoveAction">Remove Item</a></td>
                        </tr>
                        <?php
                                $item_total += ($item["price"]*$item["quantity"]);
                            }
                        ?>
                        <tr>
                            <td colspan="5" align=right><br><strong>Total:</strong> <?php echo "$".$item_total; ?><br></td>
                        </tr>
                    </table>		
                    <?php
                    }
                    ?>
                </div>
            </div>
    </body> 
</html>
