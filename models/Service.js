const { pool } = require('../config/database');

class Service {
  static async getAll(options = {}) {
    const { featured, active, limit, page = 1 } = options;
    let query = 'SELECT * FROM services WHERE 1=1';
    let params = [];

    if (featured !== undefined) {
      query += ' AND featured = ?';
      params.push(featured);
    }

    if (active !== undefined) {
      query += ' AND active = ?';
      params.push(active);
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
      console.error('Error fetching services:', error);
      throw error;
    }
  }

  static async getFeatured(limit) {
    return this.getAll({ featured: true, active: true, limit });
  }

  static async getById(id) {
    try {
      const [rows] = await pool.execute('SELECT * FROM services WHERE id = ?', [id]);
      return rows[0] || null;
    } catch (error) {
      console.error('Error fetching service by ID:', error);
      throw error;
    }
  }

  static async create(serviceData) {
    const { title, description, category, featured = false, active = true, image } = serviceData;
    
    try {
      const [result] = await pool.execute(
        'INSERT INTO services (title, description, category, featured, active, image) VALUES (?, ?, ?, ?, ?, ?)',
        [title, description, category, featured, active, image]
      );
      
      return this.getById(result.insertId);
    } catch (error) {
      console.error('Error creating service:', error);
      throw error;
    }
  }

  static async update(id, serviceData) {
    const fields = [];
    const values = [];

    Object.keys(serviceData).forEach(key => {
      if (['title', 'description', 'category', 'featured', 'active', 'image'].includes(key)) {
        fields.push(`${key} = ?`);
        values.push(serviceData[key]);
      }
    });

    if (fields.length === 0) {
      throw new Error('No valid fields to update');
    }

    values.push(id);

    try {
      await pool.execute(
        `UPDATE services SET ${fields.join(', ')} WHERE id = ?`,
        values
      );
      
      return this.getById(id);
    } catch (error) {
      console.error('Error updating service:', error);
      throw error;
    }
  }

  static async delete(id) {
    try {
      const [result] = await pool.execute('DELETE FROM services WHERE id = ?', [id]);
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error deleting service:', error);
      throw error;
    }
  }

  static async getCount(options = {}) {
    const { featured, active } = options;
    let query = 'SELECT COUNT(*) as count FROM services WHERE 1=1';
    let params = [];

    if (featured !== undefined) {
      query += ' AND featured = ?';
      params.push(featured);
    }

    if (active !== undefined) {
      query += ' AND active = ?';
      params.push(active);
    }

    try {
      const [rows] = await pool.execute(query, params);
      return rows[0].count;
    } catch (error) {
      console.error('Error counting services:', error);
      throw error;
    }
  }
}

module.exports = Service;
