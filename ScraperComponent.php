<?php

namespace App\Http\Livewire;

use Livewire\Component;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client; // Import Guzzle for reliable HTTP requests
use GuzzleHttp\Exception\ClientException; // Handle potential HTTP errors
use Illuminate\Support\Facades\Log;
class ScraperComponent extends Component
{
    public $url;
    public $allLinks = [];
    public $errorMessage = null; // Add property to store potential error messages
    public $linkCount = 0;
    public $searchWord;
    public $wordCounts = [];
    public $allLinks_search = [];
    // public $totalWordCount = 0;

    public function getAllLinks()
    {
        $links = [];
        $errorMessage = null; // Initialize error message within function

        // Validate URL format (basic check)
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            $errorMessage = "Invalid URL format. Please enter a valid URL.";
            goto handle_error; // Jump to error handling if validation fails
        }

        try {
            // Use Guzzle for robust HTTP requests
            $client = new Client();
            $response = $client->get($this->url);

            // Ensure successful response (status code 200)
            if ($response->getStatusCode() !== 200) {
                $errorMessage = "Failed to fetch content from the URL. Status code: " . $response->getStatusCode();
                goto handle_error; // Jump to error handling if request fails
            }

            $html = $response->getBody()->getContents(); // Get HTML content

            // Create DOMDocument object
            $dom = new DOMDocument();

            @$dom->loadHTML($html); // Use loadHTML for proper HTML parsing

            // Create a DOMXPath object
            $xpath = new DOMXPath($dom);

            // Query to select all anchor tags
            $anchorTags = $xpath->query('//a');

            // Loop through each anchor tag
            foreach ($anchorTags as $tag) {
                // Get the href attribute value
                $href = $tag->getAttribute('href');

                // Check if href is not empty and not a hash link
                if (!empty($href) && $href != "#") {
                    // If href starts with "http", "https", or "//", it's an absolute link
                    if (strpos($href, 'http') === 0 || strpos($href, '//') === 0) {
                        $links[] = $href;
                    } else {
                        // Otherwise, it's a relative link, so construct the absolute URL
                        $links[] = rtrim($this->url, '/') . '/' . ltrim($href, '/');
                    }
                }
            }

            $this->linkCount = count($links);

            // $this->allLinks_search 
        } catch (ClientException $e) { // Catch Guzzle exceptions for robust error handling
            $errorMessage = "Error during request: " . $e->getMessage();
            goto handle_error;
        } catch (Exception $e) { // Catch general exceptions for unexpected issues
            $errorMessage = "An unexpected error occurred: " . $e->getMessage();
            goto handle_error;
        }

        handle_error: // Label for error handling jump
        if ($errorMessage !== null) {
            $this->errorMessage = $errorMessage;
        } else {
            $this->allLinks = $links;
        }
    }
    public function searchForWord()
    {
        // Validate search word input (optional)
        if (empty($this->searchWord)) {
            $this->errorMessage = "Please enter a search word.";
            return;
        }
    
        $this->wordCounts = []; // Reset word counts array before processing new links
        //dd($this->allLinks);
        // Process each found link and search for the word

        foreach ($this->allLinks as $link) {
            try {
                $client = new Client();
                $response = $client->get($link);
                $html = $response->getBody()->getContents();
                $wordCount = substr_count(strtolower($html), strtolower($this->searchWord));
                //$wordCount = str_word_count(strtolower($html), 1, $this->searchWord);
                $this->wordCounts[$link] = $wordCount; 
                // $this->totalWordCount = $totalWordCount;
                } catch (\Exception $e) {
                Log::info("--------------------------------------------------------------------");
                Log::info($e->getMessage());
                Log::info("--------------------------------------------------------------------");
            }
        }
    }
    
    public function render()
    {
        return view('livewire.scraper-component', [
            'allLinks' => $this->allLinks,
            'errorMessage' => $this->errorMessage,
            'wordCounts' => $this->wordCounts,
        ]);
    }
}
