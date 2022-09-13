<?php

declare(strict_types=1);

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function basename;
use function current;
use function is_array;

use const UPLOAD_ERR_NO_FILE;

/**
 * Sha1 testbed
 *
 * @group      Laminas_Validator
 */
class Sha1Test extends TestCase
{
    /**
     * @psalm-return array<array-key, array{
     *     0: string|string[],
     *     1: string|array{
     *         tmp_name: string,
     *         name: string,
     *         size: int,
     *         error: int,
     *         type: string
     *     },
     *     2: bool,
     *     3: string
     * }>
     */
    public function basicBehaviorDataProvider(): array
    {
        $testFile     = __DIR__ . '/_files/picture.jpg';
        $pictureTests = [
            //    Options, isValid Param, Expected value, Expected message
            ['b2a5334847b4328e7d19d9b41fd874dffa911c98', $testFile, true,  ''],
            ['52a5334847b4328e7d19d9b41fd874dffa911c98', $testFile, false, 'fileSha1DoesNotMatch'],
            [
                ['42a5334847b4328e7d19d9b41fd874dffa911c98', 'b2a5334847b4328e7d19d9b41fd874dffa911c98'],
                $testFile,
                true,
                '',
            ],
            [
                ['42a5334847b4328e7d19d9b41fd874dffa911c98', '72a5334847b4328e7d19d9b41fd874dffa911c98'],
                $testFile,
                false,
                'fileSha1DoesNotMatch',
            ],
        ];

        $testFile    = __DIR__ . '/_files/nofile.mo';
        $noFileTests = [
            //    Options, isValid Param, Expected value, message
            ['b2a5334847b4328e7d19d9b41fd874dffa911c98', $testFile, false, 'fileSha1NotFound'],
        ];

        // Dupe data in File Upload format
        $testData = array_merge($pictureTests, $noFileTests);
        foreach ($testData as $data) {
            $fileUpload = [
                'tmp_name' => $data[1],
                'name'     => basename($data[1]),
                'size'     => 200,
                'error'    => 0,
                'type'     => 'text',
            ];
            $testData[] = [$data[0], $fileUpload, $data[2], $data[3]];
        }
        return $testData;
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    public function testBasic($options, $isValidParam, bool $expected, string $messageKey): void
    {
        $validator = new File\Sha1($options);
        $this->assertEquals($expected, $validator->isValid($isValidParam));
        if (! $expected) {
            $this->assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @param string|string[] $options
     * @param string|array $isValidParam
     */
    public function testLegacy($options, $isValidParam, bool $expected, string $messageKey): void
    {
        if (! is_array($isValidParam)) {
            $this->markTestSkipped('An array is expected for legacy compat tests');
        }

        $validator = new File\Sha1($options);
        $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
        if (! $expected) {
            $this->assertArrayHasKey($messageKey, $validator->getMessages());
        }
    }

    /**
     * Ensures that getSha1() returns expected value
     *
     * @return void
     */
    public function testgetSha1()
    {
        $validator = new File\Sha1('12345');
        $this->assertEquals(['12345' => 'sha1'], $validator->getSha1());

        $validator = new File\Sha1(['12345', '12333', '12344']);
        $this->assertEquals(['12345' => 'sha1', '12333' => 'sha1', '12344' => 'sha1'], $validator->getSha1());
    }

    /**
     * Ensures that getHash() returns expected value
     */
    public function testGetHash(): void
    {
        $validator = new File\Sha1('12345');
        $this->assertEquals(['12345' => 'sha1'], $validator->getHash());

        $validator = new File\Sha1(['12345', '12333', '12344']);
        $this->assertEquals(['12345' => 'sha1', '12333' => 'sha1', '12344' => 'sha1'], $validator->getHash());
    }

    /**
     * Ensures that setSha1() returns expected value
     *
     * @return void
     */
    public function testSetSha1()
    {
        $validator = new File\Sha1('12345');
        $validator->setSha1('12333');
        $this->assertEquals(['12333' => 'sha1'], $validator->getSha1());

        $validator->setSha1(['12321', '12121']);
        $this->assertEquals(['12321' => 'sha1', '12121' => 'sha1'], $validator->getSha1());
    }

    /**
     * Ensures that setHash() returns expected value
     */
    public function testSetHash(): void
    {
        $validator = new File\Sha1('12345');
        $validator->setHash('12333');
        $this->assertEquals(['12333' => 'sha1'], $validator->getSha1());

        $validator->setHash(['12321', '12121']);
        $this->assertEquals(['12321' => 'sha1', '12121' => 'sha1'], $validator->getSha1());
    }

    /**
     * Ensures that addSha1() returns expected value
     *
     * @return void
     */
    public function testAddSha1()
    {
        $validator = new File\Sha1('12345');
        $validator->addSha1('12344');
        $this->assertEquals(['12345' => 'sha1', '12344' => 'sha1'], $validator->getSha1());

        $validator->addSha1(['12321', '12121']);
        $this->assertEquals(
            ['12345' => 'sha1', '12344' => 'sha1', '12321' => 'sha1', '12121' => 'sha1'],
            $validator->getSha1()
        );
    }

    /**
     * Ensures that addHash() returns expected value
     */
    public function testAddHash(): void
    {
        $validator = new File\Sha1('12345');
        $validator->addHash('12344');
        $this->assertEquals(['12345' => 'sha1', '12344' => 'sha1'], $validator->getSha1());

        $validator->addHash(['12321', '12121']);
        $this->assertEquals(
            ['12345' => 'sha1', '12344' => 'sha1', '12321' => 'sha1', '12121' => 'sha1'],
            $validator->getSha1()
        );
    }

    /**
     * @group Laminas-11258
     */
    public function testLaminas11258(): void
    {
        $validator = new File\Sha1('12345');
        $this->assertFalse($validator->isValid(__DIR__ . '/_files/nofile.mo'));
        $this->assertArrayHasKey('fileSha1NotFound', $validator->getMessages());
        $this->assertStringContainsString('does not exist', current($validator->getMessages()));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new File\Sha1();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(File\Sha1::NOT_FOUND, $validator->getMessages());

        $filesArray = [
            'name'     => '',
            'size'     => 0,
            'tmp_name' => '',
            'error'    => UPLOAD_ERR_NO_FILE,
            'type'     => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(File\Sha1::NOT_FOUND, $validator->getMessages());
    }

    public function testIsValidShouldThrowInvalidArgumentExceptionForArrayNotInFilesFormat(): void
    {
        $validator = new File\Sha1();
        $value     = ['foo' => 'bar'];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');
        $validator->isValid($value);
    }
}
