<?php

use App\Domain\Model\Visitor;
use Mockery as m;

beforeEach(function () {
    // Create a mock for Phalcon Model Manager
    $mockModelManager = m::mock('Phalcon\Mvc\Model\Manager');
    
    // Create a mock for Phalcon Metadata
    $mockMetaData = m::mock('Phalcon\Mvc\Model\MetaData\Memory');
    $mockMetaData->shouldReceive('getAttributes')->andReturn(['id', 'created_at', 'last_active']);
    $mockMetaData->shouldReceive('getPrimaryKeyAttributes')->andReturn(['id']);
    $mockMetaData->shouldReceive('getDataTypes')->andReturn([
        'id' => 2, // String
        'created_at' => 2, // String
        'last_active' => 2, // String
    ]);
    
    // Inject the mocks
    Visitor::setDI(getDI());
    Visitor::setModelManager($mockModelManager);
    Visitor::setMetaData($mockMetaData);
});

afterEach(function () {
    m::close();
});

test('Visitor model includes a valid UUID after beforeCreate', function () {
    // Arrange
    $visitor = new Visitor();
    
    // Act
    $visitor->beforeCreate();
    
    // Assert
    expect($visitor->id)->toBeString();
    expect(strlen($visitor->id))->toBe(36); // UUIDs are 36 chars
    
    // Check UUID format (8-4-4-4-12 pattern)
    $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    expect(preg_match($uuidPattern, $visitor->id))->toBe(1);
});

test('Visitor model includes timestamps after beforeCreate', function () {
    // Arrange
    $visitor = new Visitor();
    
    // Act
    $visitor->beforeCreate();
    
    // Assert
    expect($visitor->created_at)->toBeString();
    expect($visitor->last_active)->toBeString();
    
    // Check that dates are valid
    $datePattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
    expect(preg_match($datePattern, $visitor->created_at))->toBe(1);
    expect(preg_match($datePattern, $visitor->last_active))->toBe(1);
});

test('Visitor model updates last_active on beforeUpdate', function () {
    // Arrange
    $visitor = new Visitor();
    $visitor->beforeCreate();
    $originalLastActive = $visitor->last_active;
    
    // Simulate a small delay
    sleep(1);
    
    // Act
    $visitor->beforeUpdate();
    
    // Assert
    expect($visitor->last_active)->not->toBe($originalLastActive);
    
    // Check that new date is valid
    $datePattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
    expect(preg_match($datePattern, $visitor->last_active))->toBe(1);
}); 