<?php

namespace app\models;

use app\core\Database;
use app\core\Model;
use app\dto\BookDTO;
use app\dto\ExternalBookDTO;
use app\util\CurrentUser;

class ModelExternalBooks extends Model
{
    public function saveInputValid($input, array &$output): bool
    {
        if(!is_array($input)) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        if(
            !isset($input['external_book_id'])
        ) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        return true;
    }

    public function saveExternalBook(array $input, array &$output): bool
    {
        $externalBook = $this->externalSearchById($input['external_book_id']);
        if($externalBook === null) {
            $output = [
                "status" => "Error",
                "message" => "Incorrect input data"
            ];
            return false;
        }

        $bookToSave = new BookDTO(
            title: $externalBook->title,
            ownerUserId: CurrentUser::getUserId(),
            text: $externalBook->text
        );

        Database::insertBook($bookToSave);
        $output = [
            "status" => "OK",
            "message" => "Book saved"
        ];
        return true;
    }

    private function externalSearchById(string $externalBookId): ?ExternalBookDTO
    {
        // id из GoogleApi состоит из цифр и букв, id из MIF состоит только из цифр
        if(ctype_digit($externalBookId)) {
            return $this->searchMIFById($externalBookId);
        } else {
            $results = $this->searchGoogleApi($externalBookId);

            if($results === null) return null;

            if(count($results) > 1) {

                $this->filterResultGoogleAPIByCaseSensitiveId($results, $externalBookId);
            }

            if(count($results) == 0) return null;

            return $results[0];
        }
    }

    /**
     * @param string $externalBookId
     * @return ExternalBookDTO|null
     */
    private function searchMIFById(string $externalBookId): ?ExternalBookDTO
    {
        $url = "https://www.mann-ivanov-ferber.ru/datasource/ajax?resourceid=" . urlencode($externalBookId);
        $json = @file_get_contents($url);
        if(!$json) return null;

        $data = json_decode($json, true);
        if(!isset($data['bookData'])) {
            return null;
        }

        return new ExternalBookDTO(
            externalBookId: $externalBookId,
            title: $data['bookData']['title'],
            text: $data['bookData']['url'],
        );
    }

    /**
     * @param ExternalBookDTO[] $results
     * @param string $correctExternalBookId
     */
    private function filterResultGoogleAPIByCaseSensitiveId(array &$results, string $correctExternalBookId): void
    {
        $results = array_filter($results, fn(ExternalBookDTO $item) => $item->externalBookId === $correctExternalBookId);
        $results = array_values($results);
    }

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
            $output = [
                "status" => "Error",
                "message" => "Results is empty"
            ];
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
        if(!isset($data['items'])) {
            return null;
        }

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
        if(!isset($data['total']) || $data['total'] == 0) {
            return null;
        }

        return array_map(fn($item) => new ExternalBookDTO(
            externalBookId: $item['id'],
            title: $item['title'],
            text: $item['url']
        ), $data['books']);
    }
}