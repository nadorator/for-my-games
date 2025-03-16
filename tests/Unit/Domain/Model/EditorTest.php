<?php

use App\Domain\Model\Editor;
use Mockery as m;

beforeEach(function () {
    // Create a mock for Phalcon Model Manager
    $mockModelManager = m::mock('Phalcon\Mvc\Model\Manager');
    
    // Create a mock for Phalcon Metadata
    $mockMetaData = m::mock('Phalcon\Mvc\Model\MetaData\Memory');
    $mockMetaData->shouldReceive('getAttributes')->andReturn(['id', 'email', 'password', 'role', 'created_at']);
    $mockMetaData->shouldReceive('getPrimaryKeyAttributes')->andReturn(['id']);
    $mockMetaData->shouldReceive('getDataTypes')->andReturn([
        'id' => 2, // String
        'email' => 2, // String
        'password' => 2, // String
        'role' => 2, // String
        'created_at' => 2, // String
    ]);
    
    // Inject the mocks
    Editor::setDI(getDI());
    Editor::setModelManager($mockModelManager);
    Editor::setMetaData($mockMetaData);
});

afterEach(function () {
    m::close();
});

test('Editor model generates UUID and timestamps on beforeCreate', function () {
    // Arrange
    $editor = new Editor();
    $editor->email = 'test@example.com';
    $editor->password = 'plaintext_password';
    $editor->role = 'admin';
    
    // Act
    $editor->beforeCreate();
    
    // Assert
    expect($editor->id)->toBeString();
    expect(strlen($editor->id))->toBe(36); // UUIDs are 36 chars
    
    // Check UUID format (8-4-4-4-12 pattern)
    $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    expect(preg_match($uuidPattern, $editor->id))->toBe(1);
    
    // Check timestamp
    $datePattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';
    expect(preg_match($datePattern, $editor->created_at))->toBe(1);
});

test('Editor model hashes password on beforeCreate', function () {
    // Arrange
    $editor = new Editor();
    $editor->email = 'test@example.com';
    $editor->password = 'plaintext_password';
    $editor->role = 'admin';
    
    // Act
    $editor->beforeCreate();
    
    // Assert
    expect($editor->password)->not->toBe('plaintext_password');
    expect(strlen($editor->password))->toBeGreaterThan(20); // Hashed passwords are longer
    
    // Verify it's a proper bcrypt hash
    expect(substr($editor->password, 0, 4))->toBe('$2y$');
});

test('Editor model doesn\'t re-hash already hashed password', function () {
    // Arrange
    $editor = new Editor();
    $editor->email = 'test@example.com';
    $editor->password = password_hash('plaintext_password', PASSWORD_BCRYPT);
    $editor->role = 'admin';
    $hashedPassword = $editor->password;
    
    // Act
    $editor->beforeCreate();
    
    // Assert
    expect($editor->password)->toBe($hashedPassword);
});

test('Editor model can validate correct credentials', function () {
    // Arrange
    $editor = new Editor();
    $editor->email = 'test@example.com';
    $editor->password = 'plaintext_password';
    $editor->role = 'admin';
    $editor->beforeCreate(); // This will hash the password
    
    // Act
    $isValid = $editor->validateCredentials('plaintext_password');
    
    // Assert
    expect($isValid)->toBeTrue();
});

test('Editor model rejects incorrect credentials', function () {
    // Arrange
    $editor = new Editor();
    $editor->email = 'test@example.com';
    $editor->password = 'plaintext_password';
    $editor->role = 'admin';
    $editor->beforeCreate(); // This will hash the password
    
    // Act
    $isValid = $editor->validateCredentials('wrong_password');
    
    // Assert
    expect($isValid)->toBeFalse();
}); 