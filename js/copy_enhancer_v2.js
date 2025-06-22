/**
 * 增强复制功能 - 分离式模板支持表情
 * 版本2.0 - 支持分开的欢迎语和结束语模板
 */

// 模板缓存
const templateCache = {
  welcome: null,
  closing: null
};

// 加载模板文件
async function loadTemplates() {
  try {
    // 并行加载两个模板文件
    const [welcomeRes, closingRes] = await Promise.all([
      fetch('templates/welcome_messages.json'),
      fetch('templates/closing_messages.json')
    ]);

    // 检查响应状态
    if (!welcomeRes.ok || !closingRes.ok) {
      throw new Error('模板加载失败');
    }

    // 解析JSON
    const [welcomeData, closingData] = await Promise.all([
      welcomeRes.json(),
      closingRes.json()
    ]);

    // 验证数据格式
    if (Array.isArray(welcomeData)) {
      templateCache.welcome = welcomeData;
    }
    if (Array.isArray(closingData)) {
      templateCache.closing = closingData;
    }

  } catch (error) {
    console.error('加载模板失败:', error);
    // 使用默认模板
    templateCache.welcome = templateCache.welcome || ["尊敬的客户您好！这是您的续保车辆信息："];
    templateCache.closing = templateCache.closing || ["感谢您一直以来的支持！"];
  }
}

// 获取随机消息
function getRandomMessage(type) {
  const messages = templateCache[type];
  if (!messages || !messages.length) return '';
  return messages[Math.floor(Math.random() * messages.length)];
}

// 增强复制功能
function enhanceCopyButtons() {
  document.addEventListener('click', async function(e) {
    const btn = e.target.closest('[data-copy-target]');
    if (!btn) return;

    e.preventDefault();
    
    // 首次加载模板
    if (!templateCache.welcome || !templateCache.closing) {
      await loadTemplates();
    }

    // 获取原始内容
    const target = document.querySelector(btn.dataset.copyTarget);
    const originalText = target?.innerText || '';

    // 组装内容
    const content = [
      getRandomMessage('welcome'),
      '',
      originalText,
      '',
      getRandomMessage('closing')
    ].join('\n');

    // 复制到剪贴板
    try {
      await navigator.clipboard.writeText(content);
      showFeedback('复制成功！', btn);
    } catch (err) {
      console.error('复制失败:', err);
      showFeedback('复制失败，请重试', btn);
    }
  });
}

// 显示反馈
function showFeedback(message, element) {
  const feedback = document.createElement('div');
  feedback.className = 'copy-feedback';
  feedback.textContent = message;
  
  element.appendChild(feedback);
  setTimeout(() => feedback.remove(), 2000);
}

// 初始化
document.addEventListener('DOMContentLoaded', () => {
  enhanceCopyButtons();
});