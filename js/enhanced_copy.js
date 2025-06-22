/**
 * 增强复制功能 - 带欢迎语和结束语
 * 使用说明：
 * 1. 复制按钮添加 class="enhanced-copy-btn"
 * 2. 车辆明细容器添加 class="vehicle-details"
 */

document.addEventListener('DOMContentLoaded', function() {
  // 获取随机模板
  async function getRandomTemplate(file) {
    try {
      const response = await fetch(file);
      if (!response.ok) throw new Error('模板加载失败');
      const text = await response.text();
      const templates = text.split('\n').filter(line => line.trim());
      return templates[Math.floor(Math.random() * templates.length)] || '';
    } catch (error) {
      console.error('加载模板失败:', error);
      return '';
    }
  }

  // 处理复制事件
  document.body.addEventListener('click', async function(e) {
    if (e.target.classList.contains('enhanced-copy-btn')) {
      e.preventDefault();
      
      // 获取车辆明细
      const detailsContainer = e.target.closest('.modal-content')?.querySelector('.vehicle-details');
      if (!detailsContainer) {
        alert('未找到车辆明细内容');
        return;
      }
      const details = detailsContainer.innerText.trim();

      // 获取模板
      const [welcome, closing] = await Promise.all([
        getRandomTemplate('templates/welcome_templates.txt'),
        getRandomTemplate('templates/closing_templates.txt')
      ]);

      // 组装内容
      const content = `${welcome}\n\n${details}\n\n${closing}`;

      // 执行复制
      try {
        await navigator.clipboard.writeText(content);
        alert('复制成功！已包含欢迎语和结束语');
      } catch (err) {
        console.error('复制失败:', err);
        alert('复制失败，请重试');
      }
    }
  });
});