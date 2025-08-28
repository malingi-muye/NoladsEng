const { pool } = require('../config/database');

class Product {
  static async getAll(options = {}) {
    const { featured, limit, page = 1, category } = options;
    let query = 'SELECT * FROM products WHERE 1=1';
    let params = [];

    if (featured !== undefined) {
      query += ' AND featured = ?';
      params.push(featured);
    }

    if (category) {
      query += ' AND category = ?';
      params.push(category);
    }

    // Add ordering
    query += ' ORDER BY created_at DESC';

    // Add pagination
    if (limit) {
      const offset = (page - 1) * limit;
      query += ' LIMIT ? OFFSET ?';
      params.push(parseInt(limit), offset);
    }

    try {
      const [rows] = await pool.execute(query, params);
      return rows;
    } catch (error) {
      console.error('Error fetching products:', error);
      throw error;
    }
  }

  static async getFeatured(limit) {
    return this.getAll({ featured: true, limit });
  }

  static async getById(id) {
    try {
      const [rows] = await pool.execute('SELECT * FROM products WHERE id = ?', [id]);
      return rows[0] || null;
    } catch (error) {
      console.error('Error fetching product by ID:', error);
      throw error;
    }
  }

  static async create(productData) {
    const { name, description, category, featured = false, price, image, stock_quantity = 0 } = productData;
    
    try {
      const [result] = await pool.execute(
        'INSERT INTO products (name, description, category, featured, price, image, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [name, description, category, featured, price, image, stock_quantity]
      );
      
      return this.getById(result.insertId);
    } catch (error) {
      console.error('Error creating product:', error);
      throw error;
    }
  }

  static async update(id, productData) {
    const fields = [];
    const values = [];

    Object.keys(productData).forEach(key => {
      if (['name', 'description', 'category', 'featured', 'price', 'image', 'stock_quantity'].includes(key)) {
        fields.push(`${key} = ?`);
        values.push(productData[key]);
      }
    });

    if (fields.length === 0) {
      throw new Error('No valid fields to update');
    }

    values.push(id);

    try {
      await pool.execute(
        `UPDATE products SET ${fields.join(', ')} WHERE id = ?`,
        values
      );
      
      return this.getById(id);
    } catch (error) {
      console.error('Error updating product:', error);
      throw error;
    }
  }

  static async delete(id) {
    try {
      const [result] = await pool.execute('DELETE FROM products WHERE id = ?', [id]);
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error deleting product:', error);
      throw error;
    }
  }

  static async updateStock(id, quantity) {
    try {
      await pool.execute(
        'UPDATE products SET stock_quantity = ? WHERE id = ?',
        [quantity, id]
      );
      
      return this.getById(id);
    } catch (error) {
      console.error('Error updating product stock:', error);
      throw error;
    }
  }

  static async getCategories() {
    try {
      const [rows] = await pool.execute(
        'SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category'
      );
      return rows.map(row => row.category);
    } catch (error) {
      console.error('Error fetching product categories:', error);
      throw error;
    }
  }

  static async getCount(options = {}) {
    const { featured, category } = options;
    let query = 'SELECT COUNT(*) as count FROM products WHERE 1=1';
    let params = [];

    if (featured !== undefined) {
      query += ' AND featured = ?';
      params.push(featured);
    }

    if (category) {
      query += ' AND category = ?';
      params.push(category);
    }

    try {
      const [rows] = await pool.execute(query, params);
      return rows[0].count;
    } catch (error) {
      console.error('Error counting products:', error);
      throw error;
    }
  }
}

module.exports = Product;
