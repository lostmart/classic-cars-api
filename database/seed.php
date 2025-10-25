<?php

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Create database connection
$dbPath = __DIR__ . '/../' . ($_ENV['DB_PATH'] ?? 'database/database.sqlite');
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Make database available globally
$GLOBALS['db'] = $db;

use App\Repositories\UserRepository;
use App\Repositories\TourRepository;
use App\Repositories\CarRepository;
use App\Repositories\BookingRepository;

echo "🌱 Starting database seeding...\n\n";

// Initialize repositories
$userRepo = new UserRepository();
$tourRepo = new TourRepository();
$carRepo = new CarRepository();
$bookingRepo = new BookingRepository();

// Create tables
echo "📋 Creating tables...\n";
$userRepo->createTable();
$tourRepo->createTable();
$carRepo->createTable();
$bookingRepo->createTable();
echo "✅ Tables created!\n\n";

// ==============================================
// SEED USERS
// ==============================================
echo "👥 Seeding users...\n";

$users = [
    // Admins
    [
        'external_user_id' => 'admin-001',
        'email' => 'admin@parisclassictours.fr',
        'first_name' => 'Sophie',
        'last_name' => 'Moreau',
        'phone' => '+33142857890',
        'role' => 'admin'
    ],
    [
        'external_user_id' => 'admin-002',
        'email' => 'manager@parisclassictours.fr',
        'first_name' => 'Thomas',
        'last_name' => 'Bernard',
        'phone' => '+33143859012',
        'role' => 'admin'
    ],
    
    // Drivers
    [
        'external_user_id' => 'driver-001',
        'email' => 'pierre.martin@parisclassictours.fr',
        'first_name' => 'Pierre',
        'last_name' => 'Martin',
        'phone' => '+33612345678',
        'role' => 'driver'
    ],
    [
        'external_user_id' => 'driver-002',
        'email' => 'jean.dubois@parisclassictours.fr',
        'first_name' => 'Jean',
        'last_name' => 'Dubois',
        'phone' => '+33623456789',
        'role' => 'driver'
    ],
    [
        'external_user_id' => 'driver-003',
        'email' => 'luc.laurent@parisclassictours.fr',
        'first_name' => 'Luc',
        'last_name' => 'Laurent',
        'phone' => '+33634567890',
        'role' => 'driver'
    ],
    [
        'external_user_id' => 'driver-004',
        'email' => 'marc.simon@parisclassictours.fr',
        'first_name' => 'Marc',
        'last_name' => 'Simon',
        'phone' => '+33645678901',
        'role' => 'driver'
    ],
    [
        'external_user_id' => 'driver-005',
        'email' => 'paul.blanc@parisclassictours.fr',
        'first_name' => 'Paul',
        'last_name' => 'Blanc',
        'phone' => '+33656789012',
        'role' => 'driver'
    ],
    
    // Customers
    [
        'external_user_id' => 'customer-001',
        'email' => 'marie.lefevre@email.fr',
        'first_name' => 'Marie',
        'last_name' => 'Lefevre',
        'phone' => '+33667890123',
        'role' => 'customer'
    ],
    [
        'external_user_id' => 'customer-002',
        'email' => 'antoine.roux@email.fr',
        'first_name' => 'Antoine',
        'last_name' => 'Roux',
        'phone' => '+33678901234',
        'role' => 'customer'
    ],
    [
        'external_user_id' => 'customer-003',
        'email' => 'camille.petit@email.fr',
        'first_name' => 'Camille',
        'last_name' => 'Petit',
        'phone' => '+33689012345',
        'role' => 'customer'
    ],
    [
        'external_user_id' => 'customer-004',
        'email' => 'lucas.garnier@email.fr',
        'first_name' => 'Lucas',
        'last_name' => 'Garnier',
        'phone' => '+33690123456',
        'role' => 'customer'
    ],
    [
        'external_user_id' => 'customer-005',
        'email' => 'emma.rousseau@email.fr',
        'first_name' => 'Emma',
        'last_name' => 'Rousseau',
        'phone' => '+33601234567',
        'role' => 'customer'
    ],
    [
        'external_user_id' => 'customer-006',
        'email' => 'hugo.denis@email.fr',
        'first_name' => 'Hugo',
        'last_name' => 'Denis',
        'phone' => '+33612345670',
        'role' => 'customer'
    ],
    [
        'external_user_id' => 'customer-007',
        'email' => 'lea.morel@email.fr',
        'first_name' => 'Léa',
        'last_name' => 'Morel',
        'phone' => '+33623456781',
        'role' => 'customer'
    ],
    [
        'external_user_id' => 'customer-008',
        'email' => 'nathan.fournier@email.fr',
        'first_name' => 'Nathan',
        'last_name' => 'Fournier',
        'phone' => '+33634567892',
        'role' => 'customer'
    ],
];

foreach ($users as $user) {
    $userId = $userRepo->insert($user);
    echo "  ✓ Created {$user['role']}: {$user['first_name']} {$user['last_name']} (ID: {$userId})\n";
}

echo "✅ Users seeded! Total: " . count($users) . "\n\n";

// ==============================================
// SEED TOURS
// ==============================================
echo "🗼 Seeding tours...\n";

$tours = [
    [
        'name' => 'Romantic Seine Tour',
        'description' => 'Cruise along the Seine passing by Notre-Dame and Eiffel Tower with stunning views of Paris landmarks',
        'duration_minutes' => 60,
        'price' => 120.00
    ],
    [
        'name' => 'Montmartre Art Tour',
        'description' => 'Explore the artistic heart of Paris including Sacré-Cœur, Place du Tertre, and charming cobblestone streets',
        'duration_minutes' => 90,
        'price' => 150.00
    ],
    [
        'name' => 'Champs-Élysées Grand Tour',
        'description' => 'Drive down the famous avenue from Arc de Triomphe to Place de la Concorde with photo stops',
        'duration_minutes' => 120,
        'price' => 180.00
    ],
    [
        'name' => 'Latin Quarter Discovery',
        'description' => 'Navigate the historic Latin Quarter including Panthéon, Sorbonne, and authentic Parisian cafés',
        'duration_minutes' => 75,
        'price' => 135.00
    ],
    [
        'name' => 'Versailles Palace Express',
        'description' => 'Day trip to the magnificent Palace of Versailles with gardens and Hall of Mirrors',
        'duration_minutes' => 240,
        'price' => 350.00
    ],
    [
        'name' => 'Paris Night Lights',
        'description' => 'Evening tour to see Paris illuminated including Eiffel Tower light show and Champs-Élysées',
        'duration_minutes' => 90,
        'price' => 165.00
    ],
];

foreach ($tours as $tour) {
    $tourId = $tourRepo->insert($tour);
    echo "  ✓ Created tour: {$tour['name']} (ID: {$tourId})\n";
}

echo "✅ Tours seeded! Total: " . count($tours) . "\n\n";

// ==============================================
// SEED CARS
// ==============================================
echo "🚗 Seeding cars...\n";

$cars = [
    [
        'make' => 'Mercedes-Benz',
        'model' => '300SL Gullwing',
        'year' => 1955,
        'color' => 'Silver',
        'capacity' => 2,
        'license_plate' => 'AB-123-CD',
        'description' => 'Iconic gullwing doors, racing heritage, and timeless design',
        'image_urls' => json_encode([
            'https://images.unsplash.com/photo-1583121274602-3e2820c69888',
            'https://images.unsplash.com/photo-1552519507-da3b142c6e3d'
        ]),
        'driver_id' => 3, // Pierre Martin
        'status' => 'available'
    ],
    [
        'make' => 'Citroën',
        'model' => '2CV',
        'year' => 1965,
        'color' => 'Baby Blue',
        'capacity' => 4,
        'license_plate' => 'EF-456-GH',
        'description' => 'Classic French charm with convertible roof, perfect for city tours',
        'image_urls' => json_encode([
            'https://images.unsplash.com/photo-1571607388263-1044f9ea01dd',
            'https://images.unsplash.com/photo-1549399542-7e3f8b79c341'
        ]),
        'driver_id' => 4, // Jean Dubois
        'status' => 'available'
    ],
    [
        'make' => 'Jaguar',
        'model' => 'E-Type',
        'year' => 1961,
        'color' => 'British Racing Green',
        'capacity' => 2,
        'license_plate' => 'IJ-789-KL',
        'description' => 'Enzo Ferrari called it "the most beautiful car ever made"',
        'image_urls' => json_encode([
            'https://images.unsplash.com/photo-1503376780353-7e6692767b70',
            'https://images.unsplash.com/photo-1494976388531-d1058494cdd8'
        ]),
        'driver_id' => 5, // Luc Laurent
        'status' => 'available'
    ],
    [
        'make' => 'Rolls-Royce',
        'model' => 'Silver Cloud',
        'year' => 1958,
        'color' => 'Burgundy',
        'capacity' => 5,
        'license_plate' => 'MN-012-OP',
        'description' => 'Ultimate luxury with leather interior and classic elegance',
        'image_urls' => json_encode([
            'https://images.unsplash.com/photo-1555215695-3004980ad54e',
            'https://images.unsplash.com/photo-1580273916550-e323be2ae537'
        ]),
        'driver_id' => 6, // Marc Simon
        'status' => 'available'
    ],
    [
        'make' => 'Porsche',
        'model' => '356 Speedster',
        'year' => 1957,
        'color' => 'Red',
        'capacity' => 2,
        'license_plate' => 'QR-345-ST',
        'description' => 'Lightweight roadster with thrilling performance and style',
        'image_urls' => json_encode([
            'https://images.unsplash.com/photo-1503376780353-7e6692767b70',
            'https://images.unsplash.com/photo-1544829099-b9a0c07fad1a'
        ]),
        'driver_id' => 7, // Paul Blanc
        'status' => 'available'
    ],
];

foreach ($cars as $car) {
    $carId = $carRepo->insert($car);
    echo "  ✓ Created car: {$car['year']} {$car['make']} {$car['model']} (ID: {$carId})\n";
}

echo "✅ Cars seeded! Total: " . count($cars) . "\n\n";

// ==============================================
// SEED BOOKINGS
// ==============================================
echo "📅 Seeding bookings...\n";

$bookings = [
    // Past completed bookings
    [
        'customer_id' => 8, // Marie Lefevre
        'tour_id' => 1, // Romantic Seine Tour
        'car_id' => 1, // Mercedes 300SL
        'booking_date' => '2025-10-01',
        'booking_time' => '10:00:00',
        'passenger_count' => 2,
        'total_price' => 120.00,
        'status' => 'completed',
        'special_requests' => 'Anniversary celebration, please bring champagne'
    ],
    [
        'customer_id' => 9, // Antoine Roux
        'tour_id' => 3, // Champs-Élysées Grand Tour
        'car_id' => 4, // Rolls-Royce
        'booking_date' => '2025-10-05',
        'booking_time' => '14:00:00',
        'passenger_count' => 4,
        'total_price' => 180.00,
        'status' => 'completed',
        'special_requests' => null
    ],
    [
        'customer_id' => 10, // Camille Petit
        'tour_id' => 2, // Montmartre Art Tour
        'car_id' => 2, // Citroën 2CV
        'booking_date' => '2025-10-10',
        'booking_time' => '09:00:00',
        'passenger_count' => 2,
        'total_price' => 150.00,
        'status' => 'completed',
        'special_requests' => 'Interested in art history, please share stories'
    ],
    
    // Confirmed upcoming bookings
    [
        'customer_id' => 11, // Lucas Garnier
        'tour_id' => 5, // Versailles Palace Express
        'car_id' => 4, // Rolls-Royce
        'booking_date' => '2025-11-15',
        'booking_time' => '08:00:00',
        'passenger_count' => 5,
        'total_price' => 350.00,
        'status' => 'confirmed',
        'special_requests' => 'Pick up from Hôtel Le Meurice at 7:45 AM'
    ],
    [
        'customer_id' => 12, // Emma Rousseau
        'tour_id' => 6, // Paris Night Lights
        'car_id' => 3, // Jaguar E-Type
        'booking_date' => '2025-11-20',
        'booking_time' => '19:00:00',
        'passenger_count' => 2,
        'total_price' => 165.00,
        'status' => 'confirmed',
        'special_requests' => 'Surprise for husband\'s birthday'
    ],
    [
        'customer_id' => 13, // Hugo Denis
        'tour_id' => 1, // Romantic Seine Tour
        'car_id' => 5, // Porsche 356
        'booking_date' => '2025-11-25',
        'booking_time' => '11:00:00',
        'passenger_count' => 2,
        'total_price' => 120.00,
        'status' => 'confirmed',
        'special_requests' => null
    ],
    
    // Pending bookings (awaiting confirmation)
    [
        'customer_id' => 14, // Léa Morel
        'tour_id' => 4, // Latin Quarter Discovery
        'car_id' => 2, // Citroën 2CV
        'booking_date' => '2025-12-01',
        'booking_time' => '15:00:00',
        'passenger_count' => 3,
        'total_price' => 135.00,
        'status' => 'pending',
        'special_requests' => 'Vegetarian lunch recommendation please'
    ],
    [
        'customer_id' => 15, // Nathan Fournier
        'tour_id' => 2, // Montmartre Art Tour
        'car_id' => 1, // Mercedes 300SL
        'booking_date' => '2025-12-10',
        'booking_time' => '10:00:00',
        'passenger_count' => 2,
        'total_price' => 150.00,
        'status' => 'pending',
        'special_requests' => null
    ],
    
    // Cancelled booking
    [
        'customer_id' => 8, // Marie Lefevre
        'tour_id' => 3, // Champs-Élysées Grand Tour
        'car_id' => 4, // Rolls-Royce
        'booking_date' => '2025-10-15',
        'booking_time' => '16:00:00',
        'passenger_count' => 4,
        'total_price' => 180.00,
        'status' => 'cancelled',
        'special_requests' => 'Changed travel plans'
    ],
];

foreach ($bookings as $booking) {
    $bookingId = $bookingRepo->insert($booking);
    $status = strtoupper($booking['status']);
    echo "  ✓ Created booking: Customer #{$booking['customer_id']} - Tour #{$booking['tour_id']} - Status: {$status} (ID: {$bookingId})\n";
}

echo "✅ Bookings seeded! Total: " . count($bookings) . "\n\n";

// ==============================================
// SUMMARY
// ==============================================
echo "🎉 Database seeding completed successfully!\n\n";
echo "📊 Summary:\n";
echo "   👥 Users: " . count($users) . " (2 admins, 5 drivers, 8 customers)\n";
echo "   🗼 Tours: " . count($tours) . "\n";
echo "   🚗 Cars: " . count($cars) . "\n";
echo "   📅 Bookings: " . count($bookings) . "\n";
echo "\n";
echo "💡 You can now test the API with this data!\n";
echo "   Example: GET http://localhost:8000/api/v1/bookings/upcoming\n";
echo "   Example: GET http://localhost:8000/api/v1/bookings/confirmed\n";
echo "   Example: GET http://localhost:8000/api/v1/bookings/pending\n";
echo "   Example: GET http://localhost:8000/api/v1/bookings/cancelled\n";
echo "\n";
echo "🚀 Let's go! 🚀\n";