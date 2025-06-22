// 表格复制增强功能
const templates = {
  wxwork: {
    lineBreak: '\n',
    divider: '─────────────────────'
  },
  default: {
    lineBreak: '\n',
    divider: '──────────────'
  }
};

let approvedTemplate = null;

function approveTemplate(templateName) {
  approvedTemplate = templates[templateName] || templates.default;
}

let welcomeMessages = [];
let closingMessages = [];

async function loadTemplates() {
  try {
    // 加载欢迎语模板
    const welcomeResponse = await fetch('templates/welcome_messages.json');
    welcomeMessages = await welcomeResponse.json();
    
    // 加载结束语模板
    const closingResponse = await fetch('templates/closing_messages.json');
    closingMessages = await closingResponse.json();
  } catch (error) {
    console.error('加载模板失败:', error);
    // 使用默认值
    welcomeMessages = [
      '尊敬的客户您好，这是您的保单信息：',
      '您好，保单信息如下：',
      '这是您需要的保单详情：'
    ];
    closingMessages = [
      '以上信息请查收，如有疑问请联系客服。',
      '感谢您的信任，祝您生活愉快！',
      '保单信息仅供参考，具体以合同为准。'
    ];
  }
}

function getRandomWelcome() {
  if (welcomeMessages.length === 0) {
    console.warn('欢迎语模板未加载，使用默认值');
    return '您好，这是您的保单信息：';
  }
  return welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)];
}

function getRandomClosing() {
  if (closingMessages.length === 0) {
    console.warn('结束语模板未加载，使用默认值');
    return '感谢您的信任，祝您生活愉快！';
  }
  return closingMessages[Math.floor(Math.random() * closingMessages.length)];
}

function getTableText(tableElement) {
  // 验证表格结构
  const headerRow = tableElement.querySelector('thead tr') || 
                   tableElement.querySelector('tr:first-child');
  if (!headerRow) {
    console.error('表格首行未找到');
    return '';
  }

  // 获取表格文本
  const tempDiv = document.createElement('div');
  tempDiv.appendChild(tableElement.cloneNode(true));
  let tableText = tempDiv.innerText;
  
  // 添加分隔线
  const divider = approvedTemplate.divider;
  if (tableText.includes('\n')) {
    tableText = tableText.replace('\n', `\n${divider}\n`);
  }

  // 格式处理
  tableText = tableText.replace(/\n\s+/g, '\n');
  return tableText.replace(/\n/g, approvedTemplate.lineBreak);
}

function showFeedback(message, element) {
  const originalText = element.textContent;
  element.textContent = message;
  setTimeout(() => {
    element.textContent = originalText;
  }, 2000);
}

async function enhanceTableCopy() {
  // 确保模板加载
  if (!approvedTemplate) approveTemplate('wxwork');
  
  // 加载消息模板
  await loadTemplates();
  console.log('消息模板加载完成');

  document.addEventListener('click', async function(e) {
    // 支持多种按钮选择方式
    const copyBtn = e.target.closest('[data-copy-table], .copy-table-btn');
    if (!copyBtn) return;
    
    e.preventDefault();
    
    // 查找表格
    const table = copyBtn.closest('.modal-content')?.querySelector('table') || 
                 document.querySelector('.renewal-table, table');
    if (!table) {
      showFeedback('未找到表格', copyBtn);
      return;
    }

    // 获取完整内容
    const welcome = getRandomWelcome();
    const closing = getRandomClosing();
    let tableText = getTableText(table);

    // 构建最终文本
    const finalText = [
      welcome,
      '',
      tableText,
      '',
      closing
    ].join('\n');
    
    // 执行复制
    try {
      await navigator.clipboard.writeText(finalText);
      showFeedback('复制成功', copyBtn);
      console.log('已复制内容:', finalText);
    } catch (err) {
      console.error('复制失败:', err);
      showFeedback('复制失败', copyBtn);
    }
  });
}

// 初始化
enhanceTableCopy();