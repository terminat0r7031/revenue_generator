<?php

$DATA_IN_PATH = "data_in/";
$DATA_OUT_PATH = "data_out/csv/";

function importData($fileName) {

    if(file($fileName)) {
        $items = array_map('str_getcsv', file($fileName));
        #print_r($items);
        $itemsAssoList = [];
        foreach($items as $item) {
            $itemsAssoList[$item[0]]["unit_price"] = $item[1]; 
        }

        return $itemsAssoList;
    }
}

function generateItemQty($totalRevenue, $items) {
    
    # Lặp cho đến khi tổng doanh thu tính được nằm trong khoảng tổng doanh thu cho trước
    $revenueOffset = ($totalRevenue * 10)/100;
    $countedRevenue = 0;
    $numOfItem = count($items);
    $index = 0;
    while (1) {
        $index ++;
        foreach($items as &$item) {
            $maxQty = round(($totalRevenue / $item["unit_price"])*0.1);
            $averageQty = round($maxQty * (1/$numOfItem));
            $magicOffset = rand(0,3) / 10;
            $guestQty = rand(rand(round($averageQty * (1-$magicOffset)), round($averageQty * (1+$magicOffset))),rand(round($maxQty * (1-$magicOffset)), round($maxQty * (1+$magicOffset))));
            $item["quantity"] = $guestQty;
            $countedRevenue += $item["unit_price"] * $item["quantity"];
        }
        if (($countedRevenue < $totalRevenue + $revenueOffset) && ($countedRevenue > $totalRevenue - $revenueOffset)) {
            // echo "Success!\n";
            // echo $countedRevenue;
            $items = json_decode(json_encode($items),true);
            $outFile = fopen("data_out/total.csv","w");
            foreach($items as $item => $details) {
                fwrite($outFile, $item . ",");
                fwrite($outFile, implode(",",$details));
                fwrite($outFile, "\n");
            } 
            return $items;
            break;
        } 
        else {
            $countedRevenue = 0;
        }
        if($index == 1000000) { # stop infinity loop
            break;
        }
    }
}

function generateItemSoldPerDay($items, $monthOfYear) {
    
    $itemsSold = [];
    $index = 0;
    $startOfMonth = new DateTime("last day of ".$monthOfYear);
    $endOfMonth = new DateTime("first day of ".$monthOfYear);
    
    $startExcelDate = dateToExcelDate($startOfMonth);
    $endExcelDate = dateToExcelDate($endOfMonth);
    
    foreach($items as $itemName => $details) {
        $countedQty = 0;
        $tempListSoldPerItem = [];
        while(1) {
            if($details["quantity"] == $countedQty) {
                foreach($tempListSoldPerItem as $element) {
                    $tempDate = rand($startExcelDate, $endExcelDate);
                    while(1) {
                        if(($tempDate >= 43501) && ($tempDate <= 43504 )) {
                            $tempDate = rand($startExcelDate, $endExcelDate);
                        }
                        else {
                            break;
                        }
                    }
                    $itemsSold[$index]["date"] = $tempDate;
                    $itemsSold[$index]["name"] = $itemName;
                    $itemsSold[$index]["unit_price"] = $details["unit_price"];
                    // $itemsSold[$index]["quantity"] = $element;
                    $itemsSold[$index]["random"] = rand(1,100000);
                    $index++;
                }
                break;
            }
            if($countedQty > $details["quantity"]){
                # clear everything
                $tempListSoldPerItem = array();
                $countedQty = 0;
            }
            $qtySold = 0;
            do {
                // $qtySold = rand(1,rand(1,round((int)$details["quantity"]*0.3)));
                $qtySold = 1;
            } while($qtySold > 10);
            
            array_push($tempListSoldPerItem, $qtySold);
            $countedQty += $qtySold;
        }
    }
    return $itemsSold;
}

function writeToCSV($data, $fileName) {
    $outFile = fopen($fileName, "w") or die("Unable to open file");
    foreach($data as $row) {
        fwrite($outFile, implode(",", $row));
        fwrite($outFile, "\n");
    }
    echo "Xong!\n";
}

function importDataWithRevenue($fileName) {
    $items = [];
    if(file($fileName)) {
        $items = array_map('str_getcsv', file($fileName));
        #print_r($items);
        $itemsAssoList = [];
        foreach($items as $item) {
            $itemsAssoList[$item[0]]["unit_price"] = $item[1]; 
            $itemsAssoList[$item[0]]["quantity"] = $item[2];
            // print_r($item);
            // echo "\n";
        }

        return $itemsAssoList;
    }
}

function dateToExcelDate($date) {
    # start point in excel is 1/1/1990
    # to calculate date in number format for excel, subtract current with start point date
    # format in PHP: YYYY-MM-dd
    $startPoint = date_create("1900-1-1");
    return date_diff($date, $startPoint)->format("%a") + 3;
}

$inFile = $DATA_IN_PATH . "thang2.csv";
$outFile = $DATA_OUT_PATH . "thang2.csv";
// $items = importData($inFile);
// $itemsWithQty = generateItemQty(50000000, $items);
$itemsWithQty = importDataWithRevenue($inFile);
$itemsSold = generateItemSoldPerDay($itemsWithQty, "2019-2");
writeToCSV($itemsSold, $outFile);

// $monthOfYear = $argv[1];
// $inFile = $argv[2];
// $outFile = $argv[3];


?>