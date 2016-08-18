<?php
// Get Solr Server Info
echo "Getting Solr Server Info...\n";
passthru('wp solr info');

// Index Solr Power items
echo "Indexing Solr Power Items...\n";
passthru('wp solr index');
echo "Indexing complete.\n";