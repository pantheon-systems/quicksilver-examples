<?php
// Post Solr schema
echo "Posting Solr Schema File...\n";
passthru('wp solr repost-schema');

// Get Solr Server Info
echo "Getting Solr Server Info...\n";
passthru('wp solr info');

// Index Solr Power items
echo "Indexing Solr Power Items...\n";
passthru('wp solr index');
echo "Indexing complete.\n";
