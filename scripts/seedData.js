const { initializeDatabase } = require('../config/database');
const Service = require('../models/Service');
const Product = require('../models/Product');
const Company = require('../models/Company');

// Sample data
const sampleServices = [
  {
    title: "Power Systems Design",
    description: "Custom electrical power distribution systems for industrial applications",
    category: "power",
    featured: true,
    active: true,
    image: "/slider/slide1.jpg"
  },
  {
    title: "Safety & Compliance",
    description: "Electrical safety audits and compliance certification services",
    category: "safety",
    featured: true,
    active: true,
    image: "/slider/slide2.jpg"
  },
  {
    title: "Industrial Automation",
    description: "Automated control systems and process optimization",
    category: "automation",
    featured: true,
    active: true,
    image: "/slider/slide3.jpg"
  },
  {
    title: "Performance Monitoring",
    description: "Real-time system monitoring and predictive maintenance",
    category: "monitoring",
    featured: true,
    active: true,
    image: "/slider/slide4.jpg"
  }
];

const sampleProducts = [
  {
    name: "Power Distribution Panel",
    description: "High-capacity electrical distribution panel for industrial use",
    category: "panels",
    featured: true,
    price: 2500.00,
    image: "/slider/slide1.jpg",
    stock_quantity: 15
  },
  {
    name: "Safety Monitoring System",
    description: "Advanced safety monitoring and alert system",
    category: "safety",
    featured: true,
    price: 3200.00,
    image: "/slider/slide2.jpg",
    stock_quantity: 8
  },
  {
    name: "Automation Controller",
    description: "Industrial automation control unit with IoT capabilities",
    category: "automation",
    featured: true,
    price: 4500.00,
    image: "/slider/slide3.jpg",
    stock_quantity: 12
  },
  {
    name: "Performance Analytics Software",
    description: "Real-time performance monitoring and analytics platform",
    category: "software",
    featured: true,
    price: 1800.00,
    image: "/slider/slide4.jpg",
    stock_quantity: 25
  }
];

const companyInfo = {
  name: "Nolads Engineering",
  description: "Leading electrical engineering services for industrial applications",
  established: 2012,
  specialties: ["Power Systems", "Safety Solutions", "Automation", "Performance Monitoring"]
};

const companyStats = {
  projects_completed: 150,
  clients_served: 85,
  years_experience: 12,
  safety_certifications: 8
};

async function seedDatabase() {
  try {
    console.log('🌱 Starting database seeding...');
    
    // Initialize database and create tables
    await initializeDatabase();
    
    // Check if data already exists
    const existingServices = await Service.getAll();
    const existingProducts = await Product.getAll();
    
    if (existingServices.length === 0) {
      console.log('📝 Seeding services...');
      for (const service of sampleServices) {
        await Service.create(service);
      }
      console.log(`✅ Created ${sampleServices.length} services`);
    } else {
      console.log('ℹ️  Services already exist, skipping...');
    }
    
    if (existingProducts.length === 0) {
      console.log('📝 Seeding products...');
      for (const product of sampleProducts) {
        await Product.create(product);
      }
      console.log(`✅ Created ${sampleProducts.length} products`);
    } else {
      console.log('ℹ️  Products already exist, skipping...');
    }
    
    // Always update company info and stats
    console.log('📝 Updating company information...');
    await Company.updateInfo(companyInfo);
    await Company.updateStats(companyStats);
    console.log('✅ Company information updated');
    
    console.log('🎉 Database seeding completed successfully!');
    
  } catch (error) {
    console.error('❌ Error seeding database:', error);
    throw error;
  }
}

// Run seeding if this file is executed directly
if (require.main === module) {
  seedDatabase()
    .then(() => {
      console.log('Seeding completed, exiting...');
      process.exit(0);
    })
    .catch((error) => {
      console.error('Seeding failed:', error);
      process.exit(1);
    });
}

module.exports = { seedDatabase };
