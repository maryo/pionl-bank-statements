<?php

declare(strict_types=1);

namespace JakubZapletal\Component\BankStatement\Tests\ABO;

use JakubZapletal\Component\BankStatement\ABO\BankAccountParser;
use PHPUnit\Framework\TestCase;

class BankAccountParserTest extends TestCase
{
    public function data(): array
    {
        return [
            'FIO      1' => ['0740000002100199001AAAAAAA AAAA AAAAAAA29072200000000000000+00000000000000+000000000000000000000000000000170290722FIO', '2100199001/2010'],
            'RB       1' => ['0740000000973789052AAAAAAA AAAA AAAAAAA31082200000000000000+00000000000000+000000000000000000000000000000225310822', '973789052/0000'],
            'KB       1' => ['0747204021470000115AAAAAAA AAAA AAAAAAA30082200000000000000+00000000000000+000000000000000000000000000000171310822CZ220100MB', '115-214740207/0100'],
            'KB       2' => ['0747289364750000043AAAAAAA AAAA AAAAAAA03112200000000000000+00000000000000+000000000000000000000000000000225041122CZ940100MB', '43-3647590287/0100'],
            'Creditas 1' => ['0740000000105559337Banka Creditas, a.s.01102200000000000000+00000000000000+000000000000000000000000000000006311022', '105559337/2250'],
        ];
    }

    /**
     * @dataProvider data
     */
    public function testParser(string $line, string $expected): void
    {
        $result = (new BankAccountParser())->parse($line);

        $this->assertEquals($expected, $result);
    }
}
