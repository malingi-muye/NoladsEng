const { pool } = require('../config/database');

class Company {
  static async getInfo() {
    try {
      const [rows] = await pool.execute('SELECT * FROM company_info ORDER BY id DESC LIMIT 1');
      return rows[0] || null;
    } catch (error) {
      console.error('Error fetching company info:', error);
      throw error;
    }
  }

  static async updateInfo(companyData) {
    const { name, description, established, specialties } = companyData;
    
    try {
      // Check if record exists
      const existing = await this.getInfo();
      
      if (existing) {
        // Update existing record
        await pool.execute(
          'UPDATE company_info SET name = ?, description = ?, established = ?, specialties = ? WHERE id = ?',
          [name, description, established, JSON.stringify(specialties), existing.id]
        );
      } else {
        // Create new record
        await pool.execute(
          'INSERT INTO company_info (name, description, established, specialties) VALUES (?, ?, ?, ?)',
          [name, description, established, JSON.stringify(specialties)]
        );
      }
      
      return this.getInfo();
    } catch (error) {
      console.error('Error updating company info:', error);
      throw error;
    }
  }

  static async getStats() {
    try {
      const [rows] = await pool.execute('SELECT stat_key, stat_value, display_name FROM company_stats');
      
      // Convert to object format
      const stats = {};
      rows.forEach(row => {
        stats[row.stat_key] = row.stat_value;
      });
      
      return stats;
    } catch (error) {
      console.error('Error fetching company stats:', error);
      throw error;
    }
  }

  static async updateStat(statKey, statValue, displayName = null) {
    try {
      await pool.execute(
        `INSERT INTO company_stats (stat_key, stat_value, display_name) 
         VALUES (?, ?, ?) 
         ON DUPLICATE KEY UPDATE 
         stat_value = VALUES(stat_value), 
         display_name = VALUES(display_name)`,
        [statKey, statValue, displayName]
      );
      
      return this.getStats();
    } catch (error) {
      console.error('Error updating company stat:', error);
      throw error;
    }
  }

  static async updateStats(statsData) {
    const connection = await pool.getConnection();
    
    try {
      await connection.beginTransaction();
      
      for (const [key, value] of Object.entries(statsData)) {
        await connection.execute(
          `INSERT INTO company_stats (stat_key, stat_value) 
           VALUES (?, ?) 
           ON DUPLICATE KEY UPDATE 
           stat_value = VALUES(stat_value)`,
          [key, value.toString()]
        );
      }
      
      await connection.commit();
      return this.getStats();
    } catch (error) {
      await connection.rollback();
      console.error('Error updating company stats:', error);
      throw error;
    } finally {
      connection.release();
    }
  }

  static async deleteStat(statKey) {
    try {
      const [result] = await pool.execute('DELETE FROM company_stats WHERE stat_key = ?', [statKey]);
      return result.affectedRows > 0;
    } catch (error) {
      console.error('Error deleting company stat:', error);
      throw error;
    }
  }
}

module.exports = Company;
