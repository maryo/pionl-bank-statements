<?php

declare(strict_types=1);

namespace JakubZapletal\Component\BankStatement\ABO;

/**
 * @internal
 */
class BankAccountParser implements BankAccountParserInterface
{
    const KB = '0100';

    private array $bankCodesWithInternalFormat = [
        self::KB => true,
    ];

    private array $bankCodesBasedOnAccountName = [
        'Banka Creditas, a.s.' => '2250',
    ];

    /**
     * Takes header line from ABO format and constructs bank account number. Line looks like:
     * 0740000002100199001AA....00000170290722FIO and output looks like 2100199001/2010 or 115-214740207/0100 or
     * 973789052/0000, etc.
     *
     * @see BankAccountParserTest
     */
    public function parse(string $line): string
    {
        $bankCode = $this->getBankCode($line);

        list($prefix, $number) = $this->getPrefixAndNumber($bankCode, $line);

        // Remove leading zeros from prefix and number and build an array of non empty string values
        $accountNumberParts = array_filter([
            ltrim($prefix, '0'),
            ltrim($number, '0')
        ], fn(string $value) => $value !== '');

        // Implode will add '-' separator between prefix and number only if $prefix is not and empty string
        // (built array will not contain empty prefix due the array_filter usage).
        return implode('-', $accountNumberParts) . '/' . $bankCode;
    }

    /**
     * @param string $bankCode
     * @param string $line
     *
     * @return array{0: string, 1: string}
     */
    protected function getPrefixAndNumber(string $bankCode, string $line): array
    {
        if (array_key_exists($bankCode, $this->bankCodesWithInternalFormat) === false) {
            return [
                substr($line, 3, 6),
                substr($line, 9, 10)
            ];
        }

        // Example: 7204021470000115
        $internalAccountNumber = substr($line, 3, 16);
        // Internal format of account number is created through permutation according to the following principle:
        // Normal format:   N01 N02 N03 N04 N05 N06 N07 N08 N09 N10 N11 N12 N13 N14 N15 N16
        // Internal format: N16 N14 N15 N12 N07 N08 N09 N10 N11 N13 N01 N02 N03 N04 N05 N06
        // @link https://mojebanka.kb.cz//file/cs/bdsk_format_abo_sk.pdf
        $internalFormatPositionToNormal = [16, 14, 15, 12, 7, 8, 9, 10, 11, 13, 1, 2, 3, 4, 5, 6];

        $prefix = '000000';
        $prefixLength = strlen($prefix);
        $number = '000000000';

        foreach ($internalFormatPositionToNormal as $index => $position) {
            $value = $internalAccountNumber[$index];

            // Convert character position to character index
            $newIndex = $position - 1;

            if ($position > $prefixLength) {
                $number[$newIndex] = $value;
            } else {
                $prefix[$newIndex - $prefixLength] = $value;
            }
        }

        // Example result: {0: '000115', 1: '0214740207'}
        return [$prefix, $number];
    }

    protected function getBankCode(string $line): string
    {
        $iban = trim(substr($line, 114, 8));

        if ($iban === 'FIO') {
            return '2010';
        }

        if (str_starts_with($iban, 'CZ') === true) {
            // Example: CZ220100 -> 0100
            return substr($iban, 4, 4);
        }

        $accountName = substr($line, 19, 20);

        if (array_key_exists($accountName, $this->bankCodesBasedOnAccountName) === true) {
            // Example: CZ220100 -> 0100
            return $this->bankCodesBasedOnAccountName[$accountName];
        }

        return '0000';
    }
}
