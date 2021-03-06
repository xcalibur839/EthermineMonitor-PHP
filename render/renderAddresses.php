<?php
    require_once "renderCharts.php";

    function renderAddresses(&$conn) {
        // Render container div
        print <<< TopDiv
    <div id="body">
        <div id="addresses">
TopDiv;

        // Attempt to retrieve Wallet data from DB from most to least active workers
        if($result = mysqli_query($conn, "SELECT * FROM Wallets ORDER BY ActiveWorkers DESC")) {
            // Retrieve the current value of ETH for value calculations
            $value = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT Coins.Coin, CoinValues.ValueSymbol, CoinValues.CoinValue
                FROM Coins, CoinValues
                WHERE Coins.Coin = 'ETH'
                AND Coins.Coin_id = CoinValues.Coin_id
                LIMIT 1"
            ));

            // Prepare and render each address section
            while($record = mysqli_fetch_assoc($result)) {
                $name = $record["Name"];
                $address = $record["Address"];
                $ethermineURL = ethermineURL;
                $etherchainURL = etherchainURL;
                $style = "width: " . ((99 / count(ADDRESSES)) - 1.4) . "%;";
            
                $primaryCurrency = $value["ValueSymbol"];
            
                $activeWorkers = $record["ActiveWorkers"];
            
                print <<< HEADER

            <div id="{$name}Section" class="addressSection $name" style="$style">
                <div class="addressData">
                    <span class="header">$name's Miner</span>
                    <span class="subHeader">Active Workers: $activeWorkers</span>
                    <span class="subHeader">Wallet:</span>
                    <a target="_blank" href="$ethermineURL$address">$address</a>
                    <a target="_blank" href="$etherchainURL$address">(etherchain.org)</a>
                    <ul class="addressDataList">
HEADER;

                // Prepare and render the miner data for this address if it was active recently
                if($record["LastSeen"] > 0) {
                    $unpaidETH = $record["Unpaid"];
                    $unpaidFiat = isset($value) ?
                        number_format(($unpaidETH * $value["CoinValue"]), 2) :
                        0;
                        
                    $hashrateCurrent = $record["Current"];
                    $hashrateReported = $record["Reported"];
                    $hashrateAverage = $record["Average"];
                    $valid = $record["Valid"];
                        
                    $payrateETHWeek = number_format(($record["PerMinute"] * perWeekOffset), 6);
                    $payrateUSDWeek = number_format(($record["PerMinute"] * $value["CoinValue"] * perWeekOffset), 2);
                
                    $payrateETHMonth = number_format(($record["PerMinute"] * perMonthOffset), 6);
                    $payrateUSDMonth = number_format(($record["PerMinute"] * $value["CoinValue"] * perMonthOffset), 2);
                
                    $payrateETHYear = number_format(($record["PerMinute"] * perYearOffset), 6);
                    $payrateUSDYear = number_format(($record["PerMinute"] * $value["CoinValue"] * perYearOffset), 2);
                
                    $lastSeen = date(timeFormat, $record["LastSeen"]);
                    $lastRefresh = date(timeFormat, $record["LastRefresh"]);
                
                    print <<< DATA

                        <li>Unpaid ETH: $unpaidETH (\${$unpaidFiat} $primaryCurrency)</li>
                        <li>Hashrate:
                            <ul>
                                <li>Current: $hashrateCurrent Mh/s ($valid shares)</li>
                                <li>Reported: $hashrateReported Mh/s</li>
                                <li>Average: $hashrateAverage Mh/s</li>
                            </ul>
                        </li>
                        <li>ETH ($primaryCurrency) per
                            <ul>
                                <li>Week: $payrateETHWeek (\${$payrateUSDWeek})</li>
                                <li>Month: $payrateETHMonth (\${$payrateUSDMonth})</li>
                                <li>Year: $payrateETHYear (\${$payrateUSDYear})</li>
                            </ul>
                        </li>
                        <li>Miner last seen: $lastSeen</li>
                        <li>Last pool refresh: $lastRefresh</li>
                    </ul>
                </div>
                <div class="addressChart" id="{$name}ChartContainer"></div>
            </div>      
DATA;
                    renderAddressChart($conn, $record);
                }
                else { // Render the nodata item if the miner hasn't been active recently
                    print <<< NODATA

                        <li id="nodata">No Data</li>
                    </ul>
                </div>
            </div>

NODATA;
                }
            }
        }

        // Close container div
        print <<< BottomDiv
        </div>
    </div>

BottomDiv;
    }
?>