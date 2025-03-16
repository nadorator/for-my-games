<?php

use App\Infrastructure\Auth\JwtService;

test('JwtService can generate a token', function () {
    // Arrange
    $jwtService = new JwtService('test_secret', 3600);
    $editorId = 'test-editor-id';
    $role = 'admin';
    
    // Act
    $token = $jwtService->generateToken($editorId, $role);
    
    // Assert
    expect($token)->toBeString();
    expect(strlen($token))->toBeGreaterThan(20);
});

test('JwtService can validate a valid token', function () {
    // Arrange
    $jwtService = new JwtService('test_secret', 3600);
    $editorId = 'test-editor-id';
    $role = 'admin';
    
    // Act
    $token = $jwtService->generateToken($editorId, $role);
    $payload = $jwtService->validateToken($token);
    
    // Assert
    expect($payload)->not->toBeNull();
    expect($payload->editor_id)->toBe($editorId);
    expect($payload->role)->toBe($role);
    expect($payload->exp)->toBeGreaterThan(time());
});

test('JwtService returns null for invalid token', function () {
    // Arrange
    $jwtService = new JwtService('test_secret', 3600);
    $invalidToken = 'invalid.token.here';
    
    // Act
    $payload = $jwtService->validateToken($invalidToken);
    
    // Assert
    expect($payload)->toBeNull();
});

test('JwtService returns null for token with wrong signature', function () {
    // Arrange
    $jwtService1 = new JwtService('test_secret1', 3600);
    $jwtService2 = new JwtService('test_secret2', 3600);
    $editorId = 'test-editor-id';
    $role = 'admin';
    
    // Act
    $token = $jwtService1->generateToken($editorId, $role);
    $payload = $jwtService2->validateToken($token);
    
    // Assert
    expect($payload)->toBeNull();
});

test('JwtService returns expected expiration date', function () {
    // Arrange
    $jwtService = new JwtService('test_secret', 3600);
    $expectedTime = date('Y-m-d\TH:i:s\Z', time() + 3600);
    
    // Act
    $expirationDate = $jwtService->getExpirationDate();
    
    // Assert
    expect($expirationDate)->toBe($expectedTime);
}); 