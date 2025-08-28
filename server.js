const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

// Import database and models
const { initializeDatabase } = require('./config/database');
const Service = require('./models/Service');
const Product = require('./models/Product');
const Company = require('./models/Company');
const { seedDatabase } = require('./scripts/seedData');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('.'));

// Database initialization
async function initializeApp() {
  try {
    await initializeDatabase();
    await seedDatabase();
    console.log('✅ Application initialized successfully');
  } catch (error) {
    console.error('❌ Failed to initialize application:', error);
    process.exit(1);
  }
}

// Error handling middleware
function asyncHandler(fn) {
  return (req, res, next) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
}

function errorHandler(err, req, res, next) {
  console.error('API Error:', err);
  res.status(500).json({
    success: false,
    error: 'Internal server error',
    message: process.env.NODE_ENV === 'development' ? err.message : 'Something went wrong'
  });
}

// API Routes
app.get('/api/services', asyncHandler(async (req, res) => {
  const { featured, active, limit, page } = req.query;

  const options = {};
  if (featured === 'true') options.featured = true;
  if (active === 'true') options.active = true;
  if (limit) options.limit = parseInt(limit);
  if (page) options.page = parseInt(page);

  const services = await Service.getAll(options);
  const total = await Service.getCount(options);

  res.json({
    success: true,
    data: services,
    total
  });
}));

app.get('/api/services/featured', asyncHandler(async (req, res) => {
  const { limit } = req.query;

  const services = await Service.getFeatured(limit ? parseInt(limit) : null);

  res.json({
    success: true,
    data: services,
    total: services.length
  });
}));

app.get('/api/products', asyncHandler(async (req, res) => {
  const { featured, limit, page, category } = req.query;

  const options = {};
  if (featured === 'true') options.featured = true;
  if (limit) options.limit = parseInt(limit);
  if (page) options.page = parseInt(page);
  if (category) options.category = category;

  const products = await Product.getAll(options);
  const total = await Product.getCount(options);

  res.json({
    success: true,
    data: products,
    total
  });
}));

app.get('/api/products/featured', asyncHandler(async (req, res) => {
  const { limit } = req.query;

  const products = await Product.getFeatured(limit ? parseInt(limit) : null);

  res.json({
    success: true,
    data: products,
    total: products.length
  });
}));

app.get('/api/products/categories', asyncHandler(async (req, res) => {
  const categories = await Product.getCategories();

  res.json({
    success: true,
    data: categories
  });
}));

app.get('/api/company/stats', asyncHandler(async (req, res) => {
  const stats = await Company.getStats();

  res.json({
    success: true,
    data: stats
  });
}));

app.get('/api/company/info', asyncHandler(async (req, res) => {
  const info = await Company.getInfo();

  if (!info) {
    return res.status(404).json({
      success: false,
      error: 'Company information not found'
    });
  }

  // Parse specialties JSON if it's a string
  if (typeof info.specialties === 'string') {
    info.specialties = JSON.parse(info.specialties);
  }

  res.json({
    success: true,
    data: info
  });
}));

// Additional API endpoints
app.get('/api/services/:id', asyncHandler(async (req, res) => {
  const service = await Service.getById(req.params.id);

  if (!service) {
    return res.status(404).json({
      success: false,
      error: 'Service not found'
    });
  }

  res.json({
    success: true,
    data: service
  });
}));

app.get('/api/products/:id', asyncHandler(async (req, res) => {
  const product = await Product.getById(req.params.id);

  if (!product) {
    return res.status(404).json({
      success: false,
      error: 'Product not found'
    });
  }

  res.json({
    success: true,
    data: product
  });
}));

// Error handling middleware
app.use(errorHandler);

// Catch-all for SPA routing
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

// Initialize and start server
async function startServer() {
  await initializeApp();

  app.listen(PORT, () => {
    console.log(`🚀 Server running on port ${PORT}`);
    console.log(`📱 Frontend: http://localhost:${PORT}`);
    console.log(`🔌 API: http://localhost:${PORT}/api`);
  });
}

startServer().catch(error => {
  console.error('❌ Failed to start server:', error);
  process.exit(1);
});
