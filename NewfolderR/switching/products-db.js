// products-db.js - Complete Product Database

const productDatabase = {
    products: [
        {
            id: "1",
            name: "Study Snack Pack",
            category: "snacks",
            description: "A variety of healthy and tasty snacks to fuel your study sessions.",
            price: 12.99,
            images: ["images/studypack/zoshua-colah-4bhS2Slawkg-unsplash.jpg"],
            rating: 4.5,
            reviewCount: 48,
            featured: true,
            stock: 15
        },
        {
            id: "2",
            name: "Essential Hygiene Kit",
            category: "personal-care",
            description: "All the personal care items you need for university life.",
            price: 19.99,
            images: ["images/products/hygiene-kit.jpg"],
            rating: 5,
            reviewCount: 36,
            featured: true,
            stock: 10
        },
        {
            id: "3",
            name: "Academic Stationery Set",
            category: "stationery",
            description: "High-quality stationery essentials for your academic success.",
            price: 15.99,
            images: ["images/products/stationery-set.jpg"],
            rating: 4,
            reviewCount: 29,
            featured: true,
            stock: 20
        },
        {
            id: "4",
            name: "Dorm Room Comfort Pack",
            category: "comfort",
            description: "Make your dorm room feel like home with these comfort essentials.",
            price: 24.99,
            images: ["images/products/comfort-pack.jpg"],
            rating: 3.5,
            reviewCount: 22,
            featured: true,
            stock: 8
        },
        {
            id: "5",
            name: "Midnight Munchies Box",
            category: "snacks",
            description: "Perfect for those late-night study sessions when you need a boost.",
            price: 14.99,
            images: ["images/products/midnight-munchies.jpg"],
            rating: 4,
            reviewCount: 32,
            featured: false,
            stock: 12
        },
        {
            id: "6",
            name: "Healthy Boost Bundle",
            category: "snacks",
            description: "Nutritious snacks and drinks to keep your energy levels up.",
            price: 16.99,
            images: ["images/products/healthy-boost.jpg"],
            rating: 5,
            reviewCount: 41,
            featured: false,
            stock: 18
        },
        {
            id: "7",
            name: "Shower Essentials Pack",
            category: "personal-care",
            description: "Everything you need for shared bathroom facilities at university.",
            price: 15.99,
            images: ["images/products/shower-essentials.jpg"],
            rating: 3.5,
            reviewCount: 27,
            featured: false,
            stock: 7
        },
        {
            id: "8",
            name: "First Aid Essentials",
            category: "personal-care",
            description: "A compact first aid kit with all the basics for minor emergencies.",
            price: 12.99,
            images: ["images/products/first-aid.jpg"],
            rating: 4,
            reviewCount: 19,
            featured: false,
            stock: 25
        },
        {
            id: "9",
            name: "Note-Taking Bundle",
            category: "stationery",
            description: "Premium notebooks, pens, and highlighters for effective note-taking.",
            price: 18.99,
            images: ["images/products/note-taking-bundle.jpg"],
            rating: 4.5,
            reviewCount: 33,
            featured: false,
            stock: 14
        },
        {
            id: "10",
            name: "Exam Preparation Kit",
            category: "stationery",
            description: "Everything you need for exam season, including revision cards and tools.",
            price: 14.99,
            images: ["images/products/exam-kit.jpg"],
            rating: 4,
            reviewCount: 25,
            featured: false,
            stock: 9
        },
        {
            id: "11",
            name: "Cozy Blanket & Pillow Set",
            category: "comfort",
            description: "Super soft blanket and cushion for those cold winter nights at university.",
            price: 29.99,
            images: ["images/products/blanket-pillow-set.jpg"],
            rating: 5,
            reviewCount: 38,
            featured: false,
            stock: 5
        },
        {
            id: "12",
            name: "Desk Lamp & Organizer",
            category: "comfort",
            description: "Adjustable desk lamp with built-in organizer for your study space.",
            price: 22.99,
            images: ["images/products/desk-lamp.jpg"],
            rating: 4,
            reviewCount: 19,
            featured: false,
            stock: 11
        },
        {
            id: "13",
            name: "Stress Relief Kit",
            category: "wellness",
            description: "Essential items to help manage stress during exam periods.",
            price: 17.99,
            images: ["images/products/stress-relief.jpg"],
            rating: 4.5,
            reviewCount: 45,
            featured: false,
            stock: 13
        },
        {
            id: "14",
            name: "Sleep & Relaxation Pack",
            category: "wellness",
            description: "Products to help you get better sleep in a noisy dorm environment.",
            price: 19.99,
            images: ["images/products/sleep-pack.jpg"],
            rating: 4,
            reviewCount: 31,
            featured: false,
            stock: 6
        },
        {
            id: "15",
            name: "Immune Support Bundle",
            category: "wellness",
            description: "Vitamins, supplements, and products to boost your immune system.",
            price: 21.99,
            images: ["images/products/immune-bundle.jpg"],
            rating: 5,
            reviewCount: 27,
            featured: false,
            stock: 16
        }
    ],

    // ======================
    // DATABASE METHODS
    // ======================
    
    getProductById: function(id) {
        return this.products.find(product => product.id === id);
    },
    
    getProductsByCategory: function(category) {
        return category === 'all' 
            ? this.products 
            : this.products.filter(product => product.category === category);
    },
    
    getFeaturedProducts: function() {
        return this.products.filter(product => product.featured);
    },
    
    searchProducts: function(query) {
        const term = query.toLowerCase();
        return this.products.filter(product => 
            product.name.toLowerCase().includes(term) || 
            product.description.toLowerCase().includes(term) ||
            product.category.toLowerCase().includes(term)
        );
    },
    
    getSortedProducts: function(products, sortBy) {
        const sorted = [...products];
        sorted.sort((a, b) => {
            switch(sortBy) {
                case 'price-low': return a.price - b.price;
                case 'price-high': return b.price - a.price;
                case 'newest': return b.id - a.id;
                case 'popularity': return b.reviewCount - a.reviewCount;
                default: return 0;
            }
        });
        return sorted;
    },
    
    getInStockProducts: function() {
        return this.products.filter(product => product.stock > 0);
    },
    
    getLowStockProducts: function() {
        return this.products.filter(product => product.stock > 0 && product.stock < 5);
    },
    
    updateStock: function(productId, quantity) {
        const product = this.getProductById(productId);
        if (product) {
            product.stock -= quantity;
            return true;
        }
        return false;
    }
};

// ======================
// INITIALIZATION
// ======================

// Initialize when DOM is loaded (handled by product-display.js)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Product database loaded with', productDatabase.products.length, 'products');
});