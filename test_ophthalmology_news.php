<?php
/**
 * Debug script to test Ophthalmology News RSS feeds
 * This file helps diagnose issues with RSS feed parsing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Ophthalmology News RSS Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .feed { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa; }
        .feed h2 { margin-top: 0; color: #0066cc; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: #666; font-size: 0.9em; margin: 5px 0; }
        .article { margin: 10px 0; padding: 10px; background: white; border-left: 3px solid #0066cc; }
        .article-title { font-weight: bold; color: #333; }
        .article-link { color: #0066cc; text-decoration: none; }
        .article-link:hover { text-decoration: underline; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .stats { background: #e8f4f8; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Ophthalmology News RSS Feeds Debug</h1>
        <p>Testing RSS feed parsing and data extraction...</p>
";

    // RSS Feeds to test
    $feeds = [
        [
            'url' => 'https://bjo.bmj.com/rss/current.xml',
            'icon' => '📄',
            'source' => 'BJO',
            'category' => 'research'
        ],
        [
            'url' => 'https://www.nature.com/eye.rss',
            'icon' => '👁️',
            'source' => 'Nature Eye',
            'category' => 'research'
        ],
        [
            'url' => 'https://www.medpagetoday.com/rss/ophthalmology.xml',
            'icon' => '📖',
            'source' => 'MedPage Today',
            'category' => 'clinical'
        ],
        [
            'url' => 'https://www.retina-specialist.com/rss',
            'icon' => '🔍',
            'source' => 'Retina Specialist',
            'category' => 'clinical'
        ],
        [
            'url' => 'https://retinatoday.com/rss',
            'icon' => '👁️',
            'source' => 'Retina Today',
            'category' => 'clinical'
        ],
        [
            'url' => 'https://feeds.feedburner.com/MedicalNewsToday-Ophthalmology',
            'icon' => '📰',
            'source' => 'Medical News Today',
            'category' => 'news'
        ],
        [
            'url' => 'https://www.healio.com/rss/ophthalmology',
            'icon' => '📋',
            'source' => 'Healio',
            'category' => 'news'
        ]
    ];

$totalArticles = 0;
$successfulFeeds = 0;
$failedFeeds = 0;

foreach ($feeds as $feed) {
    echo "<div class='feed'>";
    echo "<h2>{$feed['icon']} {$feed['source']} ({$feed['category']})</h2>";
    echo "<div class='info'>URL: <a href='{$feed['url']}' target='_blank'>{$feed['url']}</a></div>";
    
    // Fetch RSS feed
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $feed['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/rss+xml, application/xml, text/xml, */*',
        'Accept-Language: en-US,en;q=0.9'
    ]);
    
    $xmlContent = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        echo "<div class='error'>❌ cURL Error: {$curlError}</div>";
        $failedFeeds++;
        echo "</div>";
        continue;
    }
    
    if ($httpCode !== 200) {
        echo "<div class='error'>❌ HTTP Error: {$httpCode}</div>";
        $failedFeeds++;
        echo "</div>";
        continue;
    }
    
    if (empty($xmlContent) || strlen($xmlContent) < 100) {
        echo "<div class='error'>❌ Empty or too short response (Size: " . strlen($xmlContent) . " bytes)</div>";
        $failedFeeds++;
        echo "</div>";
        continue;
    }
    
    echo "<div class='success'>✅ Feed fetched successfully (Size: " . strlen($xmlContent) . " bytes)</div>";
    
    // Parse XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlContent);
    $xmlErrors = libxml_get_errors();
    libxml_clear_errors();
    
    if (!$xml) {
        echo "<div class='error'>❌ Failed to parse XML</div>";
        if (!empty($xmlErrors)) {
            echo "<pre>XML Errors:\n";
            foreach ($xmlErrors as $error) {
                echo "  - " . trim($error->message) . "\n";
            }
            echo "</pre>";
        }
        echo "<pre>First 500 chars of XML:\n" . htmlspecialchars(substr($xmlContent, 0, 500)) . "...</pre>";
        $failedFeeds++;
        echo "</div>";
        continue;
    }
    
    echo "<div class='success'>✅ XML parsed successfully</div>";
    echo "<div class='info'>XML Root: " . $xml->getName() . "</div>";
    
    // Register namespaces
    $namespaces = $xml->getNamespaces(true);
    echo "<div class='info'>Namespaces found: " . count($namespaces) . "</div>";
    if (!empty($namespaces)) {
        echo "<pre>Namespaces:\n";
        foreach ($namespaces as $prefix => $ns) {
            echo "  {$prefix}: {$ns}\n";
        }
        echo "</pre>";
    }
    
    // Register common namespaces
    $xml->registerXPathNamespace('rss', 'http://purl.org/rss/1.0/');
    $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    foreach ($namespaces as $prefix => $ns) {
        if (!in_array($prefix, ['rss', 'rdf', 'dc'])) {
            $xml->registerXPathNamespace($prefix, $ns);
        }
    }
    
    // Try to find items
    $items = [];
    
    // Try RSS 2.0 format first
    if (isset($xml->channel->item)) {
        $items = $xml->channel->item;
        echo "<div class='info'>✅ Found items via RSS 2.0 format (channel->item)</div>";
    }
    // Try Atom format
    elseif (isset($xml->entry)) {
        $items = $xml->entry;
        echo "<div class='info'>✅ Found items via Atom format (entry)</div>";
    }
    // Try RSS 1.0 (RDF) format using xpath
    else {
        $items = $xml->xpath('//rss:item | //item | //entry');
        if (!empty($items)) {
            echo "<div class='info'>✅ Found items via xpath (RSS 1.0 RDF)</div>";
        } elseif (isset($xml->item)) {
            $items = $xml->item;
            echo "<div class='info'>✅ Found items via direct access (item)</div>";
        } else {
            echo "<div class='error'>❌ No items found</div>";
            echo "<pre>XML Structure:\n";
            echo "  Root children: " . implode(', ', array_keys((array)$xml)) . "\n";
            if (isset($xml->channel)) {
                echo "  Channel children: " . implode(', ', array_keys((array)$xml->channel)) . "\n";
            }
            echo "</pre>";
            $failedFeeds++;
            echo "</div>";
            continue;
        }
    }
    
    // Convert SimpleXMLElement to array if needed
    if ($items instanceof SimpleXMLElement && !is_array($items)) {
        $itemsArray = [];
        foreach ($items as $item) {
            $itemsArray[] = $item;
        }
        $items = $itemsArray;
    }
    
    if (empty($items)) {
        echo "<div class='error'>❌ Items array is empty</div>";
        $failedFeeds++;
        echo "</div>";
        continue;
    }
    
    echo "<div class='success'>✅ Found " . count($items) . " items</div>";
    
    // Process items
    $articles = [];
    $processedCount = 0;
    
    foreach (array_slice($items, 0, 10) as $item) {
        $title = '';
        $link = '';
        $description = '';
        $pubDate = '';
        
        // Get title
        if (isset($item->title)) {
            $title = trim((string)$item->title);
        } else {
            $titleNodes = $item->xpath('.//title | .//dc:title');
            if (!empty($titleNodes)) {
                $title = trim((string)$titleNodes[0]);
            }
        }
        
        // Get link
        if (isset($item->link)) {
            $link = trim((string)$item->link);
            if (isset($item->link['href'])) {
                $link = trim((string)$item->link['href']);
            }
        } else {
            // Try rdf:about attribute (RSS 1.0)
            $rdfAttrs = $item->attributes('http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            if (!empty($rdfAttrs) && isset($rdfAttrs['about'])) {
                $link = trim((string)$rdfAttrs['about']);
            } else {
                $attributes = $item->attributes();
                if (isset($attributes['rdf:about'])) {
                    $link = trim((string)$attributes['rdf:about']);
                } elseif (isset($attributes['about'])) {
                    $link = trim((string)$attributes['about']);
                } else {
                    $linkNodes = $item->xpath('.//link | .//dc:identifier');
                    if (!empty($linkNodes)) {
                        $link = trim((string)$linkNodes[0]);
                    }
                }
            }
        }
        
        // Get description
        if (isset($item->description)) {
            $description = (string)$item->description;
        } elseif (isset($item->summary)) {
            $description = (string)$item->summary;
        } elseif (isset($item->content)) {
            $description = (string)$item->content;
        } else {
            $descNodes = $item->xpath('.//description | .//dc:description | .//content:encoded');
            if (!empty($descNodes)) {
                $description = (string)$descNodes[0];
            }
        }
        
        // Get pubDate
        if (isset($item->pubDate)) {
            $pubDate = (string)$item->pubDate;
        } elseif (isset($item->date)) {
            $pubDate = (string)$item->date;
        } elseif (isset($item->published)) {
            $pubDate = (string)$item->published;
        } elseif (isset($item->{'dc:date'})) {
            $pubDate = (string)$item->{'dc:date'};
        } else {
            $dateNodes = $item->xpath('.//dc:date | .//pubDate');
            if (!empty($dateNodes)) {
                $pubDate = (string)$dateNodes[0];
            }
        }
        
        // Skip if no title or link
        if (empty($title) || empty($link)) {
            continue;
        }
        
        $articles[] = [
            'title' => $title,
            'link' => $link,
            'description' => strip_tags($description),
            'pubDate' => $pubDate,
            'source' => $feed['source'],
            'source_icon' => $feed['icon'],
            'category' => $feed['category']
        ];
        
        $processedCount++;
    }
    
    echo "<div class='stats'>📊 Processed: {$processedCount} articles</div>";
    
    if (empty($articles)) {
        echo "<div class='error'>❌ No valid articles extracted</div>";
        $failedFeeds++;
    } else {
        echo "<div class='success'>✅ Successfully extracted " . count($articles) . " articles</div>";
        $successfulFeeds++;
        $totalArticles += count($articles);
        
        // Display articles
        echo "<h3>Articles:</h3>";
        foreach ($articles as $article) {
            echo "<div class='article'>";
            echo "<div class='article-title'>{$feed['icon']} <a href='{$article['link']}' target='_blank' class='article-link'>{$article['title']}</a></div>";
            if (!empty($article['pubDate'])) {
                echo "<div class='info'>📅 Date: {$article['pubDate']}</div>";
            }
            if (!empty($article['description'])) {
                $desc = substr(strip_tags($article['description']), 0, 150);
                echo "<div class='info'>📝 " . htmlspecialchars($desc) . "...</div>";
            }
            echo "</div>";
        }
    }
    
    echo "</div>";
}

// Summary
echo "<div class='feed' style='background: #e8f4f8; border: 2px solid #0066cc;'>";
echo "<h2>📊 Summary</h2>";
echo "<div class='stats'>";
echo "<div>✅ Successful feeds: {$successfulFeeds} / " . count($feeds) . "</div>";
echo "<div>❌ Failed feeds: {$failedFeeds} / " . count($feeds) . "</div>";
echo "<div>📰 Total articles extracted: {$totalArticles}</div>";
echo "</div>";
echo "</div>";

echo "</div></body></html>";
?>
