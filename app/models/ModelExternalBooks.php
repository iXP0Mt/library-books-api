<?php

namespace app\models;

use app\core\Model;
use app\dto\ExternalBookDTO;

class ModelExternalBooks extends Model
{
    public function isValid(array &$output): bool
    {
        if(
            !isset($_GET['q'])
        ) {
            $output = [
                "status" => "Error",
                "message" => "Empty search query"
            ];
            return false;
        }

        return true;
    }

    public function getSearchQuery(): string
    {
        return $_GET['q'];
    }

    public function externalSearch(string $q, array &$output): bool
    {
        $booksGoogleApi = $this->searchGoogleApi($q);
        $booksMIF = $this->searchMIF($q);

        if($booksGoogleApi === null && $booksMIF === null) {
            return false;
        }

        $books = array_merge($booksGoogleApi, $booksMIF);

        $output['items'] = array_map(fn(ExternalBookDTO $book) => [
            "external_book_id" => $book->externalBookId,
            "title" => $book->title,
            "text" => $book->text,
        ], $books);
        return true;
    }

    /**
     * @param string $q
     * @return ExternalBookDTO[]|null
     */
    private function searchGoogleApi(string $q): ?array
    {
        $url = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($q);
        $json = @file_get_contents($url);
        if(!$json) return null;

        $data = json_decode($json, true);

        return array_map(fn($item) => new ExternalBookDTO(
            externalBookId: $item['id'],
            title: $item['volumeInfo']['title'] ?? "Untitled",
            text: $item['volumeInfo']['description'] ?? $item['volumeInfo']['canonicalVolumeLink'] ?? null,
        ), $data['items']);
    }

    /**
     * @param string $q
     * @return ExternalBookDTO[]|null
     */
    private function searchMIF(string $q): ?array
    {
        $url = "https://www.mann-ivanov-ferber.ru/book/search.ajax?q=" . urlencode($q);
        $json = @file_get_contents($url);
        if(!$json) return null;

        $data = json_decode($json, true);

        return array_map(fn($item) => new ExternalBookDTO(
            externalBookId: $item['id'],
            title: $item['title'],
            text: $item['url']
        ), $data['books']);
    }
}