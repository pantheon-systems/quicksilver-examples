<?php
// Post Solr schema
echo "Posting Solr Schema File...\n";
passthru('wp solr repost-schema 2>&1');

// Get Solr Server Info
echo "Getting Solr Server Info...\n";
passthru('wp solr info 2>&1');

// Index Solr Power items
echo "Indexing Solr Power Items...\n";
passthru('wp solr index 2>&1');
echo "Indexing complete.\n";
